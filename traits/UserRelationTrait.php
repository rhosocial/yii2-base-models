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

use rhosocial\base\models\models\BaseUserModel;
use rhosocial\base\models\traits\MultipleBlameableTrait as mb;
use yii\base\ModelEvent;
use yii\base\InvalidConfigException;
use yii\base\InvalidValueException;
use yii\db\Connection;
use yii\db\IntegrityException;

/**
 * Relation features.
 * This trait should be used in user relation model which is extended from
 * [[BaseBlameableModel]], and is specified `$hostClass` property. And the user
 * class should be extended from [[BaseUserModel]], or any other classes used
 * [[UserTrait]].
 * Notice: Several methods associated with "inserting", "updating" and "removing" may
 * involve more DB operations, I strongly recommend those methods to be placed in
 * transaction execution, in order to ensure data consistency.
 * If you want to use group feature, the class used [[UserRelationGroupTrait]]
 * must be used coordinately.
 * @property array $groupGuids the guid array of all groups which owned by current relation.
 * @property-read array $favoriteRules
 * @property boolean $isFavorite
 * @property-read static $opposite
 * @property-read array $otherGuidRules
 * @property string $remark
 * @property-read array $remarkRules
 * @property-read array $userRelationRules
 * @property-read mixed $group
 * @property-read array $groupMembers
 * @property-read array $allGroups
 * @property-read array $nonGroupMembers
 * @property-read integer $groupsCount
 * @property-read array $groupsRules
 *
 * @method mixed createGroup(BaseUserModel $user, array $config = [])
 * @method array|false addGroup(mixed $blame)
 * @method array|false addOrCreateGroup(mixed &$blame = null, BaseUserModel $user = null)
 * @method array|false removeGroup(mixed $blame)
 * @method array|false removeAllGroups()
 * @method mixed getGroup(string $blameGuid)
 * @method mixed getOrCreateGroup(string $blameGuid, BaseUserModel $user = null))
 * @method array getGroupMembers(mixed $blame) Get all members that belongs to
 * @method array getGroupGuids(bool $checkValid = false)
 * @method array|false setGroupGuids(array $guids = [], bool $checkValid = false)
 * @method array getOwnGroups() Get all groups that owned this relation.
 * @method array setOwnGroups(array $blames)
 * @method array isGroupContained(mixed $blame)
 * @method array getAllGroups() Get all groups created by whom created this relation.
 * @method array getNonGroupMembers(BaseUserModel $user) Get members that do not belong to any group.
 * @method integer getGroupsCount() Get the count of groups of this relation.
 * @method array getEmptyGroups() Get the groups which does not contain any relations.
 * @method array getGroupsRules() Get rules associated with group attribute.
 *
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
trait UserRelationTrait
{
    use mb,
        MutualTrait {
        mb::addBlame as addGroup;
        mb::createBlame as createGroup;
        mb::addOrCreateBlame as addOrCreateGroup;
        mb::removeBlame as removeGroup;
        mb::removeAllBlames as removeAllGroups;
        mb::getBlame as getGroup;
        mb::getOrCreateBlame as getOrCreateGroup;
        mb::getBlameds as getGroupMembers;
        mb::getBlameGuids as getGroupGuids;
        mb::setBlameGuids as setGroupGuids;
        mb::getOwnBlames as getOwnGroups;
        mb::setOwnBlames as setOwnGroups;
        mb::isBlameOwned as isGroupContained;
        mb::getAllBlames as getAllGroups;
        mb::getNonBlameds as getNonGroupMembers;
        mb::getBlamesCount as getGroupsCount;
        mb::getEmptyBlames as getEmptyGroups;
        mb::getMultipleBlameableAttributeRules as getGroupsRules;
    }

    /**
     * @var string|false
     */
    public string|false $remarkAttribute = 'remark';
    public static int $relationSingle = 0;
    public static int $relationMutual = 1;
    public int $relationType = 1;
    public static array $relationTypes = [
        0 => 'Single',
        1 => 'Mutual',
    ];

    /**
     * @var string|false the attribute name of which determines the relation type.
     */
    public string|false $mutualTypeAttribute = 'type';
    public static int $mutualTypeNormal = 0x00;
    public static int $mutualTypeSuspend = 0x01;

    /**
     * @var array Mutual types.
     */
    public static array $mutualTypes = [
        0x00 => 'Normal',
        0x01 => 'Suspend',
    ];

    /**
     * @var string|false the attribute name of which determines the `favorite` field.
     */
    public string|false $favoriteAttribute = 'favorite';

    /**
     * Permit to build self relation.
     * @var boolean
     */
    public bool $relationSelf = false;

    /**
     * Get whether this relation is favorite or not.
     * @return ?bool
     */
    public function getIsFavorite(): ?bool
    {
        $favoriteAttribute = $this->favoriteAttribute;
        return (is_string($favoriteAttribute) && !empty($favoriteAttribute)) ?
        (int) $this->$favoriteAttribute > 0 : null;
    }

    /**
     * Set favorite.
     * @param bool $fav
     */
    public function setIsFavorite(bool $fav): ?int
    {
        $favoriteAttribute = $this->favoriteAttribute;
        return (is_string($favoriteAttribute) && !empty($favoriteAttribute)) ?
        $this->$favoriteAttribute = ($fav ? 1 : 0) : null;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), $this->getUserRelationRules());
    }

    /**
     * Validation rules associated with user relation.
     * @return array rules.
     */
    public function getUserRelationRules(): array
    {
        $rules = [];
        if ($this->relationType == static::$relationMutual) {
            $rules = [
                [[$this->mutualTypeAttribute], 'in', 'range' => array_keys(static::$mutualTypes)],
                [[$this->mutualTypeAttribute], 'default', 'value' => static::$mutualTypeNormal],
            ];
        }
        return array_merge($rules, $this->getRemarkRules(),
            $this->getFavoriteRules(),
            $this->getGroupsRules(),
        $this->getOtherGuidRules());
    }

    /**
     * Get remark.
     * @return ?string remark.
     */
    public function getRemark(): ?string
    {
        $remarkAttribute = $this->remarkAttribute;
        return (is_string($remarkAttribute) && !empty($remarkAttribute)) ? $this->$remarkAttribute : null;
    }

    /**
     * Set remark.
     * @param string $remark
     * @return ?string remark.
     */
    public function setRemark(string $remark): ?string
    {
        $remarkAttribute = $this->remarkAttribute;
        return (is_string($remarkAttribute) && !empty($remarkAttribute)) ? $this->$remarkAttribute = $remark : null;
    }

    /**
     * Validation rules associated with remark attribute.
     * @return array rules.
     */
    public function getRemarkRules(): array
    {
        return is_string($this->remarkAttribute) ? [
            [[$this->remarkAttribute], 'string'],
            [[$this->remarkAttribute], 'default', 'value' => ''],
            ] : [];
    }

    /**
     * Validation rules associated with favorites attribute.
     * @return array rules.
     */
    public function getFavoriteRules(): array
    {
        return is_string($this->favoriteAttribute) ? [
            [[$this->favoriteAttribute], 'boolean'],
            [[$this->favoriteAttribute], 'default', 'value' => 0],
            ] : [];
    }

    /**
     * Validation rules associated with other guid attribute.
     * @return array rules.
     */
    public function getOtherGuidRules(): array
    {
        return array_merge($this->getMutualRules(), [
            [[$this->otherGuidAttribute,
                $this->createdByAttribute],
                'unique',
                'targetAttribute' => [$this->otherGuidAttribute, $this->createdByAttribute]],
        ]);
    }

    /**
     * Attach events associated with user relation.
     */
    public function initUserRelationEvents(): void
    {
        $this->on(static::EVENT_INIT, [$this, 'onInitBlamesLimit']);
        $this->on(static::EVENT_NEW_RECORD_CREATED, [$this, 'onInitGroups']);
        $this->on(static::EVENT_NEW_RECORD_CREATED, [$this, 'onInitRemark']);
        $this->on(static::EVENT_MULTIPLE_BLAMES_CHANGED, [$this, 'onBlamesChanged']);
        $this->on(static::EVENT_AFTER_INSERT, [$this, 'onInsertRelation']);
        $this->on(static::EVENT_AFTER_UPDATE, [$this, 'onUpdateRelation']);
        $this->on(static::EVENT_AFTER_DELETE, [$this, 'onDeleteRelation']);
    }

    /**
     * Get opposite relation against self.
     * @return ?static
     */
    public function getOpposite(): ?static
    {
        if ($this->isNewRecord) {
            return null;
        }
        return static::find()->opposite($this->initiator, $this->recipient);
    }

    /**
     * Check whether the initiator is followed by recipient.
     * @param BaseUserModel $initiator
     * @param BaseUserModel $recipient
     * @return bool
     */
    public static function isFollowed($initiator, $recipient): bool
    {
        return static::find()->initiators($recipient)->recipients($initiator)->exists();
    }

    /**
     * Check whether the initiator is following recipient.
     * @param BaseUserModel $initiator
     * @param BaseUserModel $recipient
     * @return bool
     */
    public static function isFollowing($initiator, $recipient): bool
    {
        return static::find()->initiators($initiator)->recipients($recipient)->exists();
    }

    /**
     * Check whether the initiator is following and followed by recipient mutually (Single Relation).
     * Or check whether the initiator and recipient are friend whatever the mutual type is normal or suspend.
     * @param BaseUserModel $initiator
     * @param BaseUserModel $recipient
     * @return bool
     */
    public static function isMutual($initiator, $recipient): bool
    {
        return static::isFollowed($initiator, $recipient) && static::isFollowing($initiator, $recipient);
    }

    /**
     * Check whether the initiator is following and followed by recipient mutually (Single Relation).
     * Or check whether the initiator and recipient are friend if the mutual type is normal.
     * @param BaseUserModel $initiator
     * @param BaseUserModel $recipient
     * @return bool
     */
    public static function isFriend($initiator, $recipient): bool
    {
        $query = static::find();
        $model = $query->noInitModel;
        /* @var $model static */
        if ($model->relationType == static::$relationSingle) {
            return static::isMutual($initiator, $recipient);
        }
        if ($model->relationType == static::$relationMutual) {
            $relation = static::find()->initiators($initiator)->recipients($recipient)->
                    andWhere([$model->mutualTypeAttribute => static::$mutualTypeNormal])->exists();
            $inverse = static::find()->recipients($initiator)->initiators($recipient)->
                    andWhere([$model->mutualTypeAttribute => static::$mutualTypeNormal])->exists();
            return $relation && $inverse;
        }
        return false;
    }

    /**
     * Build new or return existed suspend mutual relation, or return null if
     * current type is not mutual.
     * @see buildRelation()
     * @param BaseUserModel|string $user Initiator or its GUID.
     * @param BaseUserModel|string $other Recipient or its GUID.
     * @return static The relation will be
     * given if exists, or return a new relation.
     */
    public static function buildSuspendRelation($user, $other)
    {
        $relation = static::buildRelation($user, $other);
        if (!$relation || $relation->relationType != static::$relationMutual) {
            return null;
        }
        $relation->setMutualType(static::$mutualTypeSuspend);
        return $relation;
    }

    /**
     * Build new or return existed normal relation.
     * The status of mutual relation will be changed to normal if it is not.
     * @see buildRelation()
     * @param BaseUserModel|string $user Initiator or its GUID.
     * @param BaseUserModel|string $other Recipient or its GUID.
     * @return static The relation will be
     * given if exists, or return a new relation.
     */
    public static function buildNormalRelation($user, $other)
    {
        $relation = static::buildRelation($user, $other);
        if (!$relation) {
            return null;
        }
        if ($relation->relationType == static::$relationMutual) {
            $relation->setMutualType(static::$mutualTypeNormal);
        }
        return $relation;
    }
    
    /**
     * Transform relation from suspend to normal.
     * Note: You should ensure the relation model is not new one.
     * @param static $relation
     * @return bool
     */
    public static function transformSuspendToNormal($relation): bool
    {
        if (!($relation instanceof static) || $relation->getIsNewRecord() ||
                $relation->relationType != static::$relationMutual) {
            return false;
        }
        $new = static::buildNormalRelation($relation->initiator, $relation->recipient);
        return $new->save() && $relation->refresh();
    }
    
    /**
     * Revert relation from normal to suspend.
     * Note: You should ensure the relation model is not new one.
     * @param static $relation
     * @return bool
     */
    public static function revertNormalToSuspend($relation): bool
    {
        if (!($relation instanceof static) || $relation->getIsNewRecord() ||
                $relation->relationType != static::$relationMutual) {
            return false;
        }
        $new = static::buildSuspendRelation($relation->initiator, $relation->recipient);
        return $new->save() && $relation->refresh();
    }

    /**
     * Build new or return existed relation between initiator and recipient.
     * If relation between initiator and recipient is not found, new relation will
     * be built. If initiator and recipient are the same one and it is not allowed
     * to build self relation, null will be given.
     * If you want to know whether the relation exists, you can check the return
     * value of `getIsNewRecord()` method.
     * @param BaseUserModel|string $user Initiator or its GUID.
     * @param BaseUserModel|string $other Recipient or its GUID.
     * @return static The relation will be
     * given if exists, or return a new relation. Or return null if not allowed
     * to build self relation,
     */
    protected static function buildRelation($user, $other)
    {
        $relationQuery = static::find()->initiators($user)->recipients($other);
        $noInit = $relationQuery->noInitModel;
        $relation = $relationQuery->one();
        if (!$relation) {
            if ($user instanceof BaseUserModel) {
                $user = $user->getGUID();
            }
            if ($other instanceof BaseUserModel) {
                $other = $other->getGUID();
            }
            if (!$noInit->relationSelf && $user == $other) {
                return null;
            }
            $relation = new static(['host' => $user, 'recipient' => $other]);
        }
        return $relation;
    }

    /**
     * Build opposite relation throughout the current relation. The opposite
     * relation will be given if existed.
     * @param static $relation
     * @return static
     */
    protected static function buildOppositeRelation($relation)
    {
        if (!$relation) {
            return null;
        }
        $opposite = static::buildRelation($relation->recipient, $relation->initiator);
        if ($relation->relationType == static::$relationSingle) {
            $opposite->relationType = static::$relationSingle;
        } elseif ($relation->relationType == static::$relationMutual) {
            $opposite->setMutualType($relation->getMutualType());
        }
        return $opposite;
    }
    
    /**
     * Get mutual type.
     * @return integer
     */
    public function getMutualType()
    {
        $btAttribute = $this->mutualTypeAttribute;
        if (is_string($btAttribute) && !empty($btAttribute)) {
            return $this->$btAttribute;
        }
        return static::$mutualTypeNormal;
    }
    
    /**
     * Set mutual type.
     * @param integer $type
     * @return integer
     */
    protected function setMutualType($type)
    {
        if (!array_key_exists($type, static::$mutualTypes)) {
            $type = static::$mutualTypeNormal;
        }
        $btAttribute = $this->mutualTypeAttribute;
        if (is_string($btAttribute) && !empty($btAttribute)) {
            return $this->$btAttribute = $type;
        }
        return static::$mutualTypeNormal;
    }
    
    /**
     * Insert relation, the process is placed in a transaction.
     * Note: This feature only support relational databases and skip all errors.
     * If you don't want to use transaction or database doesn't support it,
     * please use `save()` directly.
     * @param static $relation
     * @param ?Connection $connection
     * @return bool
     * @throws InvalidValueException
     * @throws InvalidConfigException
     * @throws IntegrityException
     */
    public static function insertRelation($relation, ?Connection $connection = null): bool
    {
        if (!($relation instanceof static)) {
            return false;
        }
        if (!$relation->getIsNewRecord()) {
            throw new InvalidValueException('This relation is not new one.');
        }
        if (!$connection && isset(\Yii::$app->db) && \Yii::$app->db instanceof Connection) {
            $connection = \Yii::$app->db;
        }
        if (!$connection) {
            throw new InvalidConfigException('Invalid database connection.');
        }
        /* @var $db Connection */
        $transaction = $connection->beginTransaction();
        try {
            if (!$relation->save()) {
                throw new IntegrityException('Relation insert failed.');
            }
            $transaction->commit();
        } catch (\Exception $ex) {
            $transaction->rollBack();
            return false;
        }
        return true;
    }
    
    /**
     * Remove relation, the process is placed in transaction.
     * Note: This feature only support relational databases and skip all errors.
     * If you don't want to use transaction or database doesn't support it,
     * please use `remove()` directly.
     * @param static $relation
     * @param Connection $connection
     * @return boolean|integer
     * @throws InvalidConfigException
     */
    public static function removeRelation($relation, Connection $connection = null)
    {
        if (!($relation instanceof static) || $relation->getIsNewRecord()) {
            return false;
        }
        
        if (!$connection && isset(\Yii::$app->db) && \Yii::$app->db instanceof Connection) {
            $connection = \Yii::$app->db;
        }
        if (!$connection) {
            throw new InvalidConfigException('Invalid database connection.');
        }
        /* @var $db Connection */
        $transaction = $connection->beginTransaction();
        try {
            $result = $relation->remove();
            $transaction->commit();
        } catch (\Exception $ex) {
            $transaction->rollBack();
            return false;
        }
        return $result;
    }

    /**
     * Remove myself.
     * @return integer|false The number of relations removed, or false if the remove
     * is unsuccessful for some reason. Note that it is possible the number of relations
     * removed is 0, even though the remove execution is successful.
     */
    public function remove()
    {
        return $this->delete();
    }

    /**
     * Remove first relation between initiator(s) and recipient(s).
     * @param BaseUserModel|string|array $user Initiator or its guid, or array of them.
     * @param BaseUserModel|string|array $other Recipient or its guid, or array of them.
     * @return integer|false The number of relations removed.
     */
    public static function removeOneRelation($user, $other)
    {
        $model = static::find()->initiators($user)->recipients($other)->one();
        if ($model instanceof static) {
            return $model->remove();
        }
        return false;
    }

    /**
     * Remove all relations between initiator(s) and recipient(s).
     * @param BaseUserModel|string|array $user Initiator or its guid, or array of them.
     * @param BaseUserModel|string|array $other Recipient or its guid, or array of them.
     * @return integer The number of relations removed.
     */
    public static function removeAllRelations($user, $other)
    {
        $rni = static::buildNoInitModel();
        return static::deleteAll([$rni->createdByAttribute => BaseUserModel::compositeGUIDs($user),
            $rni->otherGuidAttribute => BaseUserModel::compositeGUIDs($other)]);
    }

    /**
     * Get first relation between initiator(s) and recipient(s).
     * @param BaseUserModel|string|array $user Initiator or its guid, or array of them.
     * @param BaseUserModel|string|array $other Recipient or its guid, or array of them.
     * @return static
     */
    public static function findOneRelation($user, $other)
    {
        return static::find()->initiators($user)->recipients($other)->one();
    }

    /**
     * Get first opposite relation between initiator(s) and recipient(s).
     * @param BaseUserModel|string $user Initiator or its guid, or array of them.
     * @param BaseUserModel|string $other Recipient or its guid, or array of them.
     * @return static
     */
    public static function findOneOppositeRelation($user, $other)
    {
        return static::find()->initiators($other)->recipients($user)->one();
    }

    /**
     * Get user's or users' all relations, or by specified groups.
     * @param BaseUserModel|string|array $user Initiator or its GUID, or Initiators or their GUIDs.
     * @param BaseUserRelationGroupModel|string|array|null $groups UserRelationGroup
     * or its guid, or array of them. If you do not want to delimit the groups, please assign null.
     * @return array all eligible relations
     */
    public static function findOnesAllRelations($user, $groups = null)
    {
        return static::find()->initiators($user)->groups($groups)->all();
    }

    /**
     * Initialize groups attribute.
     * @param ModelEvent $event
     */
    public function onInitGroups($event): void
    {
        $sender = $event->sender;
        /* @var $sender static */
        $sender->removeAllGroups();
    }

    /**
     * Initialize remark attribute.
     * @param ModelEvent $event
     */
    public function onInitRemark($event): void
    {
        $sender = $event->sender;
        /* @var $sender static */
        $sender->setRemark('');
    }

    /**
     * The event triggered after insert new relation.
     * The opposite relation should be inserted without triggering events
     * simultaneously after new relation inserted,
     * @param ModelEvent $event
     * @throws IntegrityException throw if insert failed.
     */
    public function onInsertRelation($event): void
    {
        $sender = $event->sender;
        /* @var $sender static */
        if ($sender->relationType == static::$relationMutual) {
            $opposite = static::buildOppositeRelation($sender);
            $opposite->off(static::EVENT_AFTER_INSERT, [$opposite, 'onInsertRelation']);
            if (!$opposite->save()) {
                $opposite->recordWarnings();
                throw new IntegrityException('Reverse relation insert failed.');
            }
            $opposite->on(static::EVENT_AFTER_INSERT, [$opposite, 'onInsertRelation']);
        }
    }

    /**
     * The event triggered after update relation.
     * The opposite relation should be updated without triggering events
     * simultaneously after existed relation removed.
     * @param ModelEvent $event
     * @throws IntegrityException throw if update failed.
     */
    public function onUpdateRelation($event): void
    {
        $sender = $event->sender;
        /* @var $sender static */
        if ($sender->relationType == static::$relationMutual) {
            $opposite = static::buildOppositeRelation($sender);
            $opposite->off(static::EVENT_AFTER_UPDATE, [$opposite, 'onUpdateRelation']);
            if (!$opposite->save()) {
                $opposite->recordWarnings();
                throw new IntegrityException('Reverse relation update failed.');
            }
            $opposite->on(static::EVENT_AFTER_UPDATE, [$opposite, 'onUpdateRelation']);
        }
    }

    /**
     * The event triggered after delete relation.
     * The opposite relation should be deleted without triggering events
     * simultaneously after existed relation removed.
     * @param ModelEvent $event
     */
    public function onDeleteRelation($event): void
    {
        $sender = $event->sender;
        /* @var $sender static */
        if ($sender->relationType == static::$relationMutual) {
            $sender->off(static::EVENT_AFTER_DELETE, [$sender, 'onDeleteRelation']);
            static::removeAllRelations($sender->recipient, $sender->initiator);
            $sender->on(static::EVENT_AFTER_DELETE, [$sender, 'onDeleteRelation']);
        }
    }
}
