<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2023 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\traits;

use Yii;
use yii\base\ModelEvent;
use yii\base\InvalidConfigException;
use yii\base\InvalidArgumentException;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\db\IntegrityException;

/**
 * This trait is designed for the model who contains parent.
 *
 * The BlameableTrait use this trait by default. If you want to use this trait
 * into seperate model, please call the `initSelfBlameableEvents()` method in
 * `init()` method, like following:
 * ```php
 * public function init()
 * {
 *     $this->initSelfBlameableEvents();  // put it before parent call.
 *     parent::init();
 * }
 * ```
 *
 * The default reference ID attribute is `guid`. You can specify another attribute
 * in [[__construct()]] method.
 *
 * We strongly recommend you to set ancestor limit and children limit, and they
 * should not be too large.
 * The ancestor limit is preferably no more than 256, and children limit is no
 * more than 1024.
 * Too large number may seriously slow down the database response speed, especially
 * in updating operation.
 *
 * The data consistency between reference ID attribute and parent attribute can
 * only be ensured by my own. And update and delete operations should be placed
 * in the transaction to avoid data inconsistencies.
 * Even so, we cannot fully guarantee data consistency. Therefore, we provide a
 * method [[clearInvalidParent()]] for clearing non-existing parent node.
 *
 * @property static $parent
 * @property-read static[] $ancestors
 * @property-read string[] $ancestorChain
 * @property-read array $ancestorModels
 * @property-read static $commonAncestor
 * @property-read static[] $children
 * @property-read static[] $oldChildren
 * @property array $selfBlameableRules
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
trait SelfBlameableTrait
{

    /**
     * @var false|string attribute name of which store the parent's guid.
     * If you do not want to use self-blameable features, please set it false.
     * Or if you access any features of this trait when this parameter is false,
     * exception may be thrown.
     */
    public string|false $parentAttribute = false;

    /**
     * @var string|array rule name and parameters of parent attribute, as well
     * as self referenced ID attribute.
     */
    public string|array $parentAttributeRule = ['string', 'max' => 36];

    /**
     * @var string self referenced ID attribute.
     * If you enable self-blameable features, this parameter should be specified,
     * otherwise, exception will be thrown.
     */
    public string $refIdAttribute = 'guid';
    public static int $parentNone = 0;
    public static int $parentParent = 1;
    public static array $parentTypes = [
        0 => 'none',
        1 => 'parent',
    ];

    /**
     * @var ?string The constant determines the null parent.
     */
    public static ?string $nullParent = null;
    const SELF_REF_UPDATE_TYPE_ON_NO_ACTION = 0;
    const SELF_REF_UPDATE_TYPE_ON_RESTRICT = 1;
    const SELF_REF_UPDATE_TYPE_ON_CASCADE = 2;
    const SELF_REF_UPDATE_TYPE_ON_SET_NULL = 3;
    const SELF_REF_UPDATE_TYPES = [
        self::SELF_REF_UPDATE_TYPE_ON_NO_ACTION => 'on action',
        self::SELF_REF_UPDATE_TYPE_ON_RESTRICT => 'restrict',
        self::SELF_REF_UPDATE_TYPE_ON_CASCADE => 'cascade',
        self::SELF_REF_UPDATE_TYPE_ON_SET_NULL => 'set null',
    ];

    /**
     * @var integer indicates the on delete type. default to cascade.
     */
    public int $onDeleteType = self::SELF_REF_UPDATE_TYPE_ON_CASCADE;

    /**
     * @var integer indicates the on update type. default to cascade.
     */
    public int $onUpdateType = self::SELF_REF_UPDATE_TYPE_ON_CASCADE;

    /**
     * @var boolean indicates whether throw exception or not when restriction occurred on updating or deleting operation.
     */
    public bool $throwRestrictException = false;

    /**
     * @var array store the attribute validation rules.
     * If this field is a non-empty array, then it will be given.
     */
    private array $localSelfBlameableRules = [];
    const EVENT_PARENT_CHANGED = 'parentChanged';
    const EVENT_CHILD_ADDED = 'childAdded';

    /**
     * @var false|integer Set the limit of ancestor level. False is no limit.
     * We strongly recommend you set an unsigned integer which is less than 256.
     */
    public int|false|null $ancestorLimit = false;

    /**
     * @var false|int|null Set the limit of children (not descendants). False is no limit.
     * We strongly recommend you set an unsigned integer which is less than 1024.
     */
    public int|false|null $childrenLimit = false;

    /**
     * Get rules associated with self blamable attribute.
     * If self-blamable rules has been stored locally, then it will be given,
     * or return the parent attribute rule.
     * @return array rules.
     */
    public function getSelfBlameableRules(): array
    {
        if (!is_string($this->parentAttribute)) {
            return [];
        }
        if (!empty($this->localSelfBlameableRules) && is_array($this->localSelfBlameableRules)) {
            return $this->localSelfBlameableRules;
        }
        if (is_string($this->parentAttributeRule)) {
            $this->parentAttributeRule = [$this->parentAttributeRule];
        }
        $this->localSelfBlameableRules = [
            array_merge([$this->parentAttribute], $this->parentAttributeRule),
        ];
        return $this->localSelfBlameableRules;
    }

    /**
     * Set rules associated with self blamable attribute.
     * @param array $rules rules.
     */
    public function setSelfBlameableRules($rules = []): void
    {
        $this->localSelfBlameableRules = $rules;
    }

    /**
     * Check whether this model has reached the ancestor limit.
     * If $ancestorLimit is false, it will be regarded as no limit(return false).
     * If $ancestorLimit is not false and not an unsigned integer, 256 will be taken.
     * @return bool
     */
    public function hasReachedAncestorLimit(): bool
    {
        if ($this->ancestorLimit === false) {
            return false;
        }
        if (!is_numeric($this->ancestorLimit) || $this->ancestorLimit < 0) {
            $this->ancestorLimit = 256;
        }
        return count($this->getAncestorChain()) >= $this->ancestorLimit;
    }

    /**
     * Check whether this model has reached the children limit.
     * If $childrenLimit is false, it will be regarded as no limit(return false).
     * If $childrenLimit is not false and not an unsigned integer, 1024 will be taken.
     * @return bool
     */
    public function hasReachedChildrenLimit(): bool
    {
        if ($this->childrenLimit === false) {
            return false;
        }
        if (!is_numeric($this->childrenLimit) || $this->childrenLimit < 0) {
            $this->childrenLimit = 1024;
        }
        return ((int) $this->getChildren()->count()) >= $this->childrenLimit;
    }

    /**
     * Bear a child.
     * The creator of this child is not necessarily the creator of current one.
     * For example: Someone commit a comment on another user's comment, these
     * two comments are father and son, but do not belong to the same owner.
     * Therefore, you need to specify the creator of current model.
     * @param array $config
     * @return false|SelfBlameableTrait|null Null if reached the ancestor limit or children limit.
     * @throws InvalidConfigException Self reference ID attribute or
     * parent attribute not determined.
     */
    public function bear(array $config = []): false|static|null
    {
        if (!$this->parentAttribute) {
            throw new InvalidConfigException("Parent Attribute Not Determined.");
        }
        if (!$this->refIdAttribute) {
            throw new InvalidConfigException("Self Reference ID Attribute Not Determined.");
        }
        if ($this->hasReachedAncestorLimit()) {
            throw new InvalidArgumentException("Reached Ancestor Limit: " . $this->ancestorLimit);
        }
        if ($this->hasReachedChildrenLimit()) {
            throw new InvalidArgumentException("Reached Children Limit: ". $this->childrenLimit);
        }
        if (isset($config['class'])) {
            unset($config['class']);
        }
        $model = new static($config);
        if ($this->addChild($model) === false) {
            return false;
        }
        return $model;
    }

    /**
     * Add a child.
     * But if children limit reached, false will be given.
     * @param static $child
     * @return bool Whether adding child succeeded or not.
     */
    public function addChild($child): bool
    {
        return $this->hasReachedChildrenLimit() ? false : $child->setParent($this);
    }

    /**
     * Event triggered before deleting itself.
     * Note: DO NOT call it directly unless you know the consequences.
     * @param ModelEvent $event
     * @return bool true if parentAttribute not specified.
     * @throws IntegrityException|Exception throw if $throwRestrictException is true when $onDeleteType is on restrict.
     */
    public function onDeleteChildren($event): bool
    {
        $sender = $event->sender;
        /* @var $sender static */
        if (empty($sender->parentAttribute) || !is_string($sender->parentAttribute)) {
            return true;
        }
        switch ($sender->onDeleteType) {
            case self::SELF_REF_UPDATE_TYPE_ON_RESTRICT:
                $event->isValid = $sender->children === null;
                if ($sender->throwRestrictException) {
                    throw new IntegrityException('Delete restricted.');
                }
                break;
            case self::SELF_REF_UPDATE_TYPE_ON_CASCADE:
                $event->isValid = $sender->deleteChildren();
                break;
            case self::SELF_REF_UPDATE_TYPE_ON_SET_NULL:
                $event->isValid = $sender->updateChildren(null);
                break;
            case self::SELF_REF_UPDATE_TYPE_ON_NO_ACTION:
            default:
                $event->isValid = true;
                break;
        }
        return true;
    }

    /**
     * Event triggered before updating itself.
     * Note: DO NOT call it directly unless you know the consequences.
     * @param ModelEvent $event
     * @return bool true if parentAttribute not specified.
     * @throws IntegrityException|Exception throw if $throwRestrictException is true when $onUpdateType is on restrict.
     */
    public function onUpdateChildren($event): bool
    {
        $sender = $event->sender;
        /* @var $sender static */
        if (empty($sender->parentAttribute) || !is_string($sender->parentAttribute)) {
            return true;
        }
        switch ($sender->onUpdateType) {
            case self::SELF_REF_UPDATE_TYPE_ON_RESTRICT:
                $event->isValid = $sender->getOldChildren() === null;
                if ($sender->throwRestrictException) {
                    throw new IntegrityException('Update restricted.');
                }
                break;
            case self::SELF_REF_UPDATE_TYPE_ON_CASCADE:
                $event->isValid = $sender->updateChildren();
                break;
            case self::SELF_REF_UPDATE_TYPE_ON_SET_NULL:
                $event->isValid = $sender->updateChildren(null);
                break;
            case self::SELF_REF_UPDATE_TYPE_ON_NO_ACTION:
            default:
                $event->isValid = true;
                break;
        }
        return true;
    }

    /**
     * Get parent query.
     * Or get parent instance if access by magic property.
     * @return ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(static::class, [$this->refIdAttribute => $this->parentAttribute]);
    }

    /**
     * Get parent ID.
     * @return string|null null if parent attribute isn't enabled.
     */
    public function getParentId(): ?string
    {
        return (is_string($this->parentAttribute) && !empty($this->parentAttribute)) ?
            $this->{$this->parentAttribute} : null;
    }

    /**
     * Set parent ID.
     * @param string $parentId
     * @return string|null null if parent attribute isn't enabled.
     */
    public function setParentId($parentId): ?string
    {
        return (is_string($this->parentAttribute) && !empty($this->parentAttribute)) ?
            $this->{$this->parentAttribute} = $parentId : null;
    }

    /**
     * Get reference ID.
     * @return ?string
     */
    public function getRefId(): ?string
    {
        if ($this->refIdAttribute == $this->guidAttribute) {
            return $this->getGUID();
        }
        if ($this->refIdAttribute == $this->idAttribute) {
            return $this->getID();
        }
        return $this->{$this->refIdAttribute};
    }

    /**
     * Set reference ID.
     * @param string $refId
     * @return string
     */
    public function setRefId($refId): string
    {
        if ($this->refIdAttribute == $this->guidAttribute) {
            return $this->setGUID($refId);
        }
        if ($this->refIdAttribute == $this->idAttribute) {
            return $this->setID($refId);
        }
        return $this->{$this->refIdAttribute} = $refId;
    }

    /**
     * Set parent.
     * Don't forget save model after setting it.
     * @param static $parent
     * @return false|string False if restriction reached. Otherwise parent's GUID given.
     */
    public function setParent($parent): false|string
    {
        if (empty($parent) || $this->getRefId() == $parent->getRefId() ||
            $parent->hasAncestor($this) || $this->hasReachedAncestorLimit()) {
            return false;
        }
        unset($this->parent);
        unset($parent->children);
        $this->trigger(self::EVENT_PARENT_CHANGED);
        $parent->trigger(self::EVENT_CHILD_ADDED);
        return $this->{$this->parentAttribute} = $parent->getRefId();
    }

    /**
     * Set null parent.
     * This method would unset the lazy loading records before setting it.
     * Don't forget save model after setting it.
     */
    public function setNullParent()
    {
        if ($this->hasParent()) {
            unset($this->parent->children);
        }
        unset($this->parent);
        $this->setParentId(static::$nullParent);
    }

    /**
     * Check whether this model has parent.
     * @return bool
     */
    public function hasParent(): bool
    {
        return $this->parent !== null;
    }

    /**
     * Check whether if $ancestor is the ancestor of myself.
     * Note: Itself will not be regarded as the ancestor.
     * @param static $ancestor
     * @return bool
     */
    public function hasAncestor($ancestor): bool
    {
        if (!$this->hasParent()) {
            return false;
        }
        if ($this->parent->getRefId() == $ancestor->getRefId()) {
            return true;
        }
        return $this->parent->hasAncestor($ancestor);
    }

    /**
     * Get ancestor chain. (Ancestors' GUID Only!)
     * If this model has ancestor, the return array consists all the ancestor in order.
     * The first element is parent, and the last element is root, otherwise return empty array.
     * If you want to get ancestor model, you can simplify instance a query and specify the
     * condition with the return value. But it will not return models under the order of ancestor chain.
     * @param string[] $ancestor
     * @return string[]
     */
    public function getAncestorChain($ancestor = []): array
    {
        if (!is_string($this->parentAttribute) || empty($this->parentAttribute)) {
            return [];
        }
        if (!$this->hasParent()) {
            return $ancestor;
        }
        $ancestor[] = $this->parent->getRefId();
        return $this->parent->getAncestorChain($ancestor);
    }

    /**
     * Get ancestors with specified ancestor chain.
     * @param string[] $ancestor Ancestor chain.
     * @return static[]|null
     */
    public static function getAncestorModels($ancestor)
    {
        if (empty($ancestor) || !is_array($ancestor)) {
            return [];
        }
        $models = [];
        foreach ($ancestor as $self) {
            $models[] = static::findOne($self);
        }
        return $models;
    }

    /**
     * Get ancestors.
     * @return static[]
     */
    public function getAncestors()
    {
        return static::getAncestorModels($this->getAncestorChain());
    }

    /**
     * Check whether if this model has common ancestor with $model.
     * @param static $model
     * @return boolean
     */
    public function hasCommonAncestor($model)
    {
        return $this->getCommonAncestor($model) !== null;
    }

    /**
     * Get common ancestor. If there isn't common ancestor, null will be given.
     * @param static $model
     * @return static
     */
    public function getCommonAncestor($model)
    {
        if (empty($this->parentAttribute) || !is_string($this->parentAttribute) ||
            empty($model) || !$model->hasParent()) {
            return null;
        }
        $ancestors = $this->getAncestorChain();
        if (in_array($model->parent->getRefId(), $ancestors)) {
            return $model->parent;
        }
        return $this->getCommonAncestor($model->parent);
    }

    /**
     * Get children query.
     * Or get children instances if access magic property.
     * @return ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(static::class, [$this->parentAttribute => $this->refIdAttribute])->inverseOf('parent');
    }

    /**
     * Get children which parent attribute point to old guid.
     * @return static[]
     */
    public function getOldChildren()
    {
        return static::find()->where([$this->parentAttribute => $this->getOldAttribute($this->refIdAttribute)])->all();
    }

    /**
     * Update all children, not grandchildren (descendants).
     * If onUpdateType is on cascade, the children will be updated automatically.
     * @param mixed $value set guid if false, set empty string if empty() return
     * true, otherwise set it to $parentAttribute.
     * @return IntegrityException|boolean true if all update operations
     * succeeded to execute, or false if anyone of them failed. If not production
     * environment or enable debug mode, it will return exception.
     * @throws IntegrityException|Exception throw if anyone update failed.
     * The exception message only contains the first error.
     */
    public function updateChildren($value = false)
    {
        $children = $this->getOldChildren();
        if (empty($children)) {
            return true;
        }
        $transaction = $this->getDb()->beginTransaction();
        try {
            foreach ($children as $child) {
                /* @var $child static */
                if ($value === false) {
                    $child->setParent($this);
                } elseif (empty($value)) {
                    $child->setNullParent();
                } else {
                    $child->setParentId($value);
                }
                if (!$child->save()) {
                    throw new IntegrityException('Update failed:', $child->getErrors());
                }
            }
            $transaction->commit();
        } catch (IntegrityException $ex) {
            $transaction->rollBack();
            if (YII_DEBUG || YII_ENV !== YII_ENV_PROD) {
                Yii::error($ex->getMessage(), __METHOD__);
                return $ex;
            }
            Yii::warning($ex->getMessage(), __METHOD__);
            return false;
        }
        return true;
    }

    /**
     * Delete all children, not grandchildren (descendants).
     * If onDeleteType is `on cascade`, the children will be deleted automatically.
     * If onDeleteType is `on restrict` and contains children, the deletion will
     * be restricted.
     * @return IntegrityException|boolean true if all delete operations
     * succeeded to execute, or false if anyone of them failed. If not production
     * environment or enable debug mode, it will return exception.
     * @throws IntegrityException|Exception throw if anyone delete failed.
     * The exception message only contains the first error.
     */
    public function deleteChildren()
    {
        $children = $this->children;
        if (empty($children)) {
            return true;
        }
        $transaction = $this->getDb()->beginTransaction();
        try {
            foreach ($children as $child) {
                if (!$child->delete()) {
                    throw new IntegrityException('Delete failed:', $child->getErrors());
                }
            }
            $transaction->commit();
        } catch (IntegrityException $ex) {
            $transaction->rollBack();
            if (YII_DEBUG || YII_ENV !== YII_ENV_PROD) {
                Yii::error($ex->getMessage(), __METHOD__);
                return $ex;
            }
            Yii::warning($ex->getMessage(), __METHOD__);
            return false;
        }
        return true;
    }

    /**
     * Update children's parent attribute.
     * Event triggered before updating.
     * @param ModelEvent $event
     * @return boolean
     */
    public function onParentRefIdChanged($event)
    {
        $sender = $event->sender;
        /* @var $sender static */
        if ($sender->isAttributeChanged($sender->refIdAttribute)) {
            return $sender->onUpdateChildren($event);
        }
    }

    /**
     * Attach events associated with self blameable attribute.
     */
    protected function initSelfBlameableEvents()
    {
        $this->on(static::EVENT_BEFORE_UPDATE, [$this, 'onParentRefIdChanged']);
        $this->on(static::EVENT_BEFORE_DELETE, [$this, 'onDeleteChildren']);
    }

    /**
     * Clear invalid parent.
     * The invalid state depends on which if parent id exists but it's corresponding
     * parent cannot be found.
     * @return bool True if parent attribute is set null, False if parent valid.
     */
    public function clearInvalidParent(): bool
    {
        if ($this->getParentId() !== static::$nullParent && !$this->hasParent()) {
            $this->setNullParent();
            return true;
        }
        return false;
    }
}
