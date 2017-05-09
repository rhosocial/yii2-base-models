<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2017 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\traits;

use rhosocial\base\helpers\Number;
use rhosocial\base\models\queries\BaseUserQuery;
use yii\base\InvalidParamException;
use yii\base\ModelEvent;
use yii\base\NotSupportedException;
use yii\behaviors\BlameableBehavior;
use yii\caching\TagDependency;
use yii\data\Pagination;

/**
 * This trait is used for building blameable model. It contains following features：
 * 1.Single-column(field) content;
 * 2.Content type;
 * 3.Content rules(generated automatically);
 * 4.Creator(owner)'s GUID;
 * 5.Updater's GUID;
 * 6.Confirmation features, provided by [[ConfirmationTrait]];
 * 7.Self referenced features, provided by [[SelfBlameableTrait]];
 * @property-read array $blameableAttributeRules Get all rules associated with
 * blameable.
 * @property array $blameableRules Get or set all the rules associated with
 * creator, updater, content and its ID, as well as all the inherited rules.
 * @property array $blameableBehaviors Get or set all the behaviors assoriated
 * with creator and updater, as well as all the inherited behaviors.
 * @property-read array $descriptionRules Get description property rules.
 * @property-read mixed $content Content.
 * @property-read boolean $contentCanBeEdited Whether this content could be edited.
 * @property-read array $contentRules Get content rules.
 * @property BserUserModel $host The owner of this model.
 * @property BaseUserModel $user The owner of this model(the same as $host).
 * @property BaseUserModel $updater The updater who updated this model latest.
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
trait BlameableTrait
{
    use ConfirmationTrait,
        SelfBlameableTrait;

    private $blameableLocalRules = [];
    private $blameableLocalBehaviors = [];

    /**
     * @var boolean|string|array Specify the attribute(s) name of content(s). If
     * there is only one content attribute, you can assign its name. Or there
     * is multiple attributes associated with contents, you can assign their
     * names in array. If you don't want to use this feature, please assign
     * false.
     * For example:
     * ```php
     * public $contentAttribute = 'comment'; // only one field named as 'comment'.
     * ```
     * or
     * ```php
     * public $contentAttribute = ['year', 'month', 'day']; // multiple fields.
     * ```
     * or
     * ```php
     * public $contentAttribute = false; // no need of this feature.
     * ```
     * If you don't need this feature, you should add rules corresponding with
     * `content` in `rules()` method of your user model by yourself.
     */
    public $contentAttribute = 'content';

    /**
     * @var array built-in validator name or validatation method name and
     * additional parameters.
     */
    public $contentAttributeRule = ['string', 'max' => 255];

    /**
     * @var boolean|string Specify the field which stores the type of content.
     */
    public $contentTypeAttribute = false;

    /**
     * @var boolean|array Specify the logic type of content, not data type. If
     * your content doesn't need this feature. please specify false. If the
     * $contentAttribute is specified to false, this attribute will be skipped.
     * ```php
     * public $contentTypes = [
     *     'public',
     *     'private',
     *     'friend',
     * ];
     * ```
     */
    public $contentTypes = false;

    /**
     * @var boolean|string This attribute speicfy the name of description
     * attribute. If this attribute is assigned to false, this feature will be
     * skipped.
     */
    public $descriptionAttribute = false;

    /**
     * @var string
     */
    public $initDescription = '';

    /**
     * @var string the attribute that will receive current user ID value. This
     * attribute must be assigned.
     */
    public $createdByAttribute = "user_guid";

    /**
     * @var string the attribute that will receive current user ID value.
     * Set this property to false if you do not want to record the updater ID.
     */
    public $updatedByAttribute = "user_guid";

    /**
     * @var boolean Add combinated unique rule if assigned to true.
     */
    public $idCreatorCombinatedUnique = true;

    /**
     * @var boolean|string The name of user class which own the current entity.
     * If this attribute is assigned to false, this feature will be skipped, and
     * when you use create() method of UserTrait, it will be assigned with
     * current user class.
     */
    //public $userClass;
    
    /**
     * @var boolean|string Host class.
     */
    public $hostClass;
    public static $cacheKeyBlameableRules = 'blameable_rules';
    public static $cacheTagBlameableRules = 'tag_blameable_rules';
    public static $cacheKeyBlameableBehaviors = 'blameable_behaviors';
    public static $cacheTagBlameableBehaviors = 'tag_blameable_behaviors';

    /**
     * @inheritdoc
     * ------------
     * The classical rules is like following:
     * [
     *     ['guid', 'required'],
     *     ['guid', 'unique'],
     *     ['guid', 'string', 'max' => 36],
     *
     *     ['id', 'required'],
     *     ['id', 'unique'],
     *     ['id', 'string', 'max' => 4],
     *
     *     ['created_at', 'safe'],
     *     ['updated_at', 'safe'],
     *
     *     ['ip_type', 'in', 'range' => [4, 6]],
     *     ['ip', 'number', 'integerOnly' => true, 'min' => 0],
     * ]
     * @return array
     */
    public function rules()
    {
        return $this->getBlameableRules();
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return $this->getBlameableBehaviors();
    }

    /**
     * Get total of contents which owned by their owner.
     * @return integer
     */
    public function countOfOwner()
    {
        $createdByAttribute = $this->createdByAttribute;
        return static::find()->where([$createdByAttribute => $this->$createdByAttribute])->count();
    }

    /**
     * Get content.
     * @return mixed
     */
    public function getContent()
    {
        $contentAttribute = $this->contentAttribute;
        if ($contentAttribute === false) {
            return null;
        }
        if (is_array($contentAttribute)) {
            $content = [];
            foreach ($contentAttribute as $key => $value) {
                $content[$key] = $this->$value;
            }
            return $content;
        }
        return $this->$contentAttribute;
    }

    /**
     * Set content.
     * @param mixed $content
     */
    public function setContent($content)
    {
        $contentAttribute = $this->contentAttribute;
        if ($contentAttribute === false) {
            return;
        }
        if (is_array($contentAttribute)) {
            foreach ($contentAttribute as $key => $value) {
                $this->$value = $content[$key];
            }
            return;
        }
        $this->$contentAttribute = $content;
    }

    /**
     * Determines whether content could be edited. Your should implement this
     * method by yourself.
     * @return boolean
     * @throws NotSupportedException
     */
    public function getContentCanBeEdited()
    {
        if ($this->contentAttribute === false) {
            return false;
        }
        throw new NotSupportedException("This method is not implemented.");
    }

    /**
     * Get blameable rules cache key.
     * @return string cache key.
     */
    public function getBlameableRulesCacheKey()
    {
        return static::class . $this->cachePrefix . static::$cacheKeyBlameableRules;
    }

    /**
     * Get blameable rules cache tag.
     * @return string cache tag
     */
    public function getBlameableRulesCacheTag()
    {
        return static::class . $this->cachePrefix . static::$cacheTagBlameableRules;
    }

    /**
     * Get the rules associated with content to be blamed.
     * @return array rules.
     */
    public function getBlameableRules()
    {
        $cache = $this->getCache();
        if ($cache) {
            $this->blameableLocalRules = $cache->get($this->getBlameableRulesCacheKey());
        }
        // 若当前规则不为空，且是数组，则认为是规则数组，直接返回。
        if (!empty($this->blameableLocalRules) && is_array($this->blameableLocalRules)) {
            return $this->blameableLocalRules;
        }

        // 父类规则与确认规则合并。
        if ($cache) {
            TagDependency::invalidate($cache, [$this->getEntityRulesCacheTag()]);
        }
        $rules = array_merge(
            parent::rules(),
            $this->getConfirmationRules(),
            $this->getBlameableAttributeRules(),
            $this->getDescriptionRules(),
            $this->getContentRules(),
            $this->getSelfBlameableRules()
        );
        $this->setBlameableRules($rules);
        return $this->blameableLocalRules;
    }

    /**
     * Get the rules associated with `createdByAttribute`, `updatedByAttribute`
     * and `idAttribute`-`createdByAttribute` combination unique.
     * @return array rules.
     * @throws NotSupportedException throws if `createdByAttribute` not set.
     */
    public function getBlameableAttributeRules()
    {
        $rules = [];
        // 创建者和上次修改者由 BlameableBehavior 负责，因此标记为安全。
        if (!is_string($this->createdByAttribute) || empty($this->createdByAttribute)) {
            throw new NotSupportedException('You must assign the creator.');
        }
        if ($this->guidAttribute != $this->createdByAttribute) {
            $rules[] = [
                [$this->createdByAttribute],
                'safe',
            ];
        }

        if (is_string($this->updatedByAttribute) && $this->guidAttribute != $this->updatedByAttribute && !empty($this->updatedByAttribute)) {
            $rules[] = [
                [$this->updatedByAttribute],
                'safe',
            ];
        }

        if ($this->idCreatorCombinatedUnique && is_string($this->idAttribute)) {
            $rules ['id'] = [
                [$this->idAttribute,
                    $this->createdByAttribute],
                'unique',
                'targetAttribute' => [$this->idAttribute,
                    $this->createdByAttribute],
            ];
        }
        return $rules;
    }
    
    public function getIdRules()
    {
        if (is_string($this->idAttribute) && !empty($this->idAttribute) && $this->idCreatorCombinatedUnique && $this->idAttributeType !== static::$idTypeAutoIncrement) {
            return [
                [[$this->idAttribute], 'required'],
            ];
        }
        return parent::getIdRules();
    }

    /**
     * Get the rules associated with `description` attribute.
     * @return array rules.
     */
    public function getDescriptionRules()
    {
        $rules = [];
        if (is_string($this->descriptionAttribute) && !empty($this->descriptionAttribute)) {
            $rules[] = [
                [$this->descriptionAttribute],
                'string'
            ];
            $rules[] = [
                [$this->descriptionAttribute],
                'default',
                'value' => $this->initDescription,
            ];
        }
        return $rules;
    }

    /**
     * Get the rules associated with `content` and `contentType` attributes.
     * @return array rules.
     */
    public function getContentRules()
    {
        if (!$this->contentAttribute) {
            return [];
        }
        $rules = [];
        $rules[] = [$this->contentAttribute, 'required'];
        if ($this->contentAttributeRule) {
            if (is_string($this->contentAttributeRule)) {
                $this->contentAttributeRule = [$this->contentAttributeRule];
            }
            if (is_array($this->contentAttributeRule)) {
                $rules[] = array_merge([$this->contentAttribute], $this->contentAttributeRule);
            }
        }

        if (!$this->contentTypeAttribute) {
            return $rules;
        }

        if (is_array($this->contentTypes) && !empty($this->contentTypes)) {
            $rules[] = [[
                $this->contentTypeAttribute],
                'required'];
            $rules[] = [[
                $this->contentTypeAttribute],
                'in',
                'range' => array_keys($this->contentTypes)];
        }
        return $rules;
    }

    /**
     * Set blameable rules.
     * @param array $rules
     */
    protected function setBlameableRules($rules = [])
    {
        $this->blameableLocalRules = $rules;
        $cache = $this->getCache();
        if ($cache) {
            $tagDependency = new TagDependency(['tags' => [$this->getBlameableRulesCacheTag()]]);
            $cache->set($this->getBlameableRulesCacheKey(), $rules, 0, $tagDependency);
        }
    }

    /**
     * Get blameable behaviors cache key.
     * @return string cache key.
     */
    public function getBlameableBehaviorsCacheKey()
    {
        return static::class . $this->cachePrefix . static::$cacheKeyBlameableBehaviors;
    }

    /**
     * Get blameable behaviors cache tag.
     * @return string cache tag.
     */
    public function getBlameableBehaviorsCacheTag()
    {
        return static::class . $this->cachePrefix . static::$cacheTagBlameableBehaviors;
    }

    /**
     * Get blameable behaviors. If current behaviors array is empty, the init
     * array will be given.
     * @return array
     */
    public function getBlameableBehaviors()
    {
        $cache = $this->getCache();
        if ($cache) {
            $this->blameableLocalBehaviors = $cache->get($this->getBlameableBehaviorsCacheKey());
        }
        if (empty($this->blameableLocalBehaviors) || !is_array($this->blameableLocalBehaviors)) {
            if ($cache) {
                TagDependency::invalidate($cache, [$this->getEntityBehaviorsCacheTag()]);
            }
            $behaviors = parent::behaviors();
            $behaviors['blameable'] = [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => $this->createdByAttribute,
                'updatedByAttribute' => $this->updatedByAttribute,
                'value' => [$this,
                    'onGetCurrentUserGuid'],
            ];
            $this->setBlameableBehaviors($behaviors);
        }
        return $this->blameableLocalBehaviors;
    }

    /**
     * Set blameable behaviors.
     * @param array $behaviors
     */
    protected function setBlameableBehaviors($behaviors = [])
    {
        $this->blameableLocalBehaviors = $behaviors;
        $cache = $this->getCache();
        if ($cache) {
            $tagDependencyConfig = ['tags' => [$this->getBlameableBehaviorsCacheTag()]];
            $tagDependency = new TagDependency($tagDependencyConfig);
            $cache->set($this->getBlameableBehaviorsCacheKey(), $behaviors, 0, $tagDependency);
        }
    }

    /**
     * Set description.
     * @return string description.
     */
    public function getDescription()
    {
        $descAttribute = $this->descriptionAttribute;
        return is_string($descAttribute) ? $this->$descAttribute : null;
    }

    /**
     * Get description.
     * @param string $desc description.
     * @return string|null description if enabled, or null if disabled.
     */
    public function setDescription($desc)
    {
        $descAttribute = $this->descriptionAttribute;
        return is_string($descAttribute) ? $this->$descAttribute = $desc : null;
    }

    /**
     * Get blame who owned this blameable model.
     * NOTICE! This method will not check whether `$hostClass` exists. You should
     * specify it in `init()` method.
     * @return BaseUserQuery user.
     */
    public function getUser()
    {
        return $this->getHost();
    }

    /**
     * Declares a `has-one` relation.
     * The declaration is returned in terms of a relational [[\yii\db\ActiveQuery]] instance
     * through which the related record can be queried and retrieved back.
     *
     * A `has-one` relation means that there is at most one related record matching
     * the criteria set by this relation, e.g., a customer has one country.
     *
     * For example, to declare the `country` relation for `Customer` class, we can write
     * the following code in the `Customer` class:
     *
     * ```php
     * public function getCountry()
     * {
     *     return $this->hasOne(Country::className(), ['id' => 'country_id']);
     * }
     * ```
     *
     * Note that in the above, the 'id' key in the `$link` parameter refers to an attribute name
     * in the related class `Country`, while the 'country_id' value refers to an attribute name
     * in the current AR class.
     *
     * Call methods declared in [[\yii\db\ActiveQuery]] to further customize the relation.
     *
     * This method is provided by [[\yii\db\BaseActiveRecord]].
     * @param string $class the class name of the related record
     * @param array $link the primary-foreign key constraint. The keys of the array refer to
     * the attributes of the record associated with the `$class` model, while the values of the
     * array refer to the corresponding attributes in **this** AR class.
     * @return \yii\dbActiveQueryInterface the relational query object.
     */
    public abstract function hasOne($class, $link);
    
    /**
     * Get host of this model.
     * @return BaseUserQuery
     */
    public function getHost()
    {
        $hostClass = $this->hostClass;
        $model = $hostClass::buildNoInitModel();
        return $this->hasOne($hostClass::className(), [$model->guidAttribute => $this->createdByAttribute]);
    }
    
    /**
     * Set host of this model.
     * @param string $host
     * @return string|boolean
     */
    public function setHost($host)
    {
        if ($host instanceof $this->hostClass || $host instanceof \yii\web\IdentityInterface) {
            return $this->{$this->createdByAttribute} = $host->getGUID();
        }
        if (is_string($host) && preg_match(Number::GUID_REGEX, $host)) {
            return $this->{$this->createdByAttribute} = Number::guid_bin($host);
        }
        if (strlen($host) == 16) {
            return $this->{$this->createdByAttribute} = $host;
        }
        return false;
    }
    
    /**
     *
     * @param BaseUserModel|string $user
     * @return boolean
     */
    public function setUser($user)
    {
        return $this->setHost($user);
    }

    /**
     * Get updater who updated this blameable model recently.
     * NOTICE! This method will not check whether `$hostClass` exists. You should
     * specify it in `init()` method.
     * @return BaseUserQuery user.
     */
    public function getUpdater()
    {
        if (!is_string($this->updatedByAttribute) || empty($this->updatedByAttribute)) {
            return null;
        }
        $hostClass = $this->hostClass;
        $model = $hostClass::buildNoInitModel();
        /* @var $model BaseUserModel */
        return $this->hasOne($hostClass::className(), [$model->guidAttribute => $this->updatedByAttribute]);
    }
    
    /**
     * Set updater.
     * @param BaseUserModel|string $updater
     * @return boolean
     */
    public function setUpdater($updater)
    {
        if (!is_string($this->updatedByAttribute) || empty($this->updatedByAttribute)) {
            return false;
        }
        if ($updater instanceof $this->hostClass || $updater instanceof \yii\web\IdentityInterface) {
            return $this->{$this->updatedByAttribute} = $updater->getGUID();
        }
        if (is_string($updater) && preg_match(Number::GUID_REGEX, $updater)) {
            return $this->{$this->updatedByAttribute} = Number::guid_bin($updater);
        }
        if (strlen($updater) == 16) {
            return $this->{$this->updatedByAttribute} = $updater;
        }
        return false;
    }

    /**
     * This event is triggered before the model update.
     * This method is ONLY used for being triggered by event. DO NOT call,
     * override or modify it directly, unless you know the consequences.
     * @param ModelEvent $event
     */
    public function onContentChanged($event)
    {
        $sender = $event->sender;
        /* @var $sender static */
        return $sender->resetConfirmation();
    }

    /**
     * Return the current user's GUID if current model doesn't specify the owner
     * yet, or return the owner's GUID if current model has been specified.
     * This method is ONLY used for being triggered by event. DO NOT call,
     * override or modify it directly, unless you know the consequences.
     * @param ModelEvent $event
     * @return string the GUID of current user or the owner.
     */
    public function onGetCurrentUserGuid($event)
    {
        $sender = $event->sender;
        /* @var $sender static */
        if (isset($sender->attributes[$sender->createdByAttribute])) {
            return $sender->attributes[$sender->createdByAttribute];
        }
        $identity = \Yii::$app->user->identity;
        /* @var $identity BaseUserModel */
        if ($identity) {
            return $identity->getGUID();
        }
    }

    /**
     * Initialize type of content. the first of element[index is 0] of
     * $contentTypes will be used.
     * @param ModelEvent $event
     */
    public function onInitContentType($event)
    {
        $sender = $event->sender;
        /* @var $sender static */
        if (!is_string($sender->contentTypeAttribute) || empty($sender->contentTypeAttribute)) {
            return;
        }
        $contentTypeAttribute = $sender->contentTypeAttribute;
        if (!isset($sender->$contentTypeAttribute) &&
            !empty($sender->contentTypes) &&
            is_array($sender->contentTypes)) {
            $sender->$contentTypeAttribute = $sender->contentTypes[0];
        }
    }

    /**
     * Initialize description property with $initDescription.
     * @param ModelEvent $event
     */
    public function onInitDescription($event)
    {
        $sender = $event->sender;
        /* @var $sender static */
        if (!is_string($sender->descriptionAttribute) || empty($sender->descriptionAttribute)) {
            return;
        }
        $descriptionAttribute = $sender->descriptionAttribute;
        if (empty($sender->$descriptionAttribute)) {
            $sender->$descriptionAttribute = $sender->initDescription;
        }
    }

    /**
     * Attaches an event handler to an event.
     *
     * The event handler must be a valid PHP callback. The following are
     * some examples:
     *
     * ```
     * function ($event) { ... }         // anonymous function
     * [$object, 'handleClick']          // $object->handleClick()
     * ['Page', 'handleClick']           // Page::handleClick()
     * 'handleClick'                     // global function handleClick()
     * ```
     *
     * The event handler must be defined with the following signature,
     *
     * ```
     * function ($event)
     * ```
     *
     * where `$event` is an [[Event]] object which includes parameters associated with the event.
     *
     * This method is provided by [[\yii\base\Component]].
     * @param string $name the event name
     * @param callable $handler the event handler
     * @param mixed $data the data to be passed to the event handler when the event is triggered.
     * When the event handler is invoked, this data can be accessed via [[Event::data]].
     * @param boolean $append whether to append new event handler to the end of the existing
     * handler list. If false, the new handler will be inserted at the beginning of the existing
     * handler list.
     * @see off()
     */
    public abstract function on($name, $handler, $data = null, $append = true);

    /**
     * Detaches an existing event handler from this component.
     * This method is the opposite of [[on()]].
     * This method is provided by [[\yii\base\Component]]
     * @param string $name event name
     * @param callable $handler the event handler to be removed.
     * If it is null, all handlers attached to the named event will be removed.
     * @return boolean if a handler is found and detached
     * @see on()
     */
    public abstract function off($name, $handler = null);

    /**
     * Attach events associated with blameable model.
     */
    public function initBlameableEvents()
    {
        $this->on(static::$eventConfirmationChanged, [$this, "onConfirmationChanged"]);
        $this->on(static::$eventNewRecordCreated, [$this, "onInitConfirmation"]);
        $contentTypeAttribute = $this->contentTypeAttribute;
        if (is_string($contentTypeAttribute) && !empty($contentTypeAttribute) && !isset($this->$contentTypeAttribute)) {
            $this->on(static::$eventNewRecordCreated, [$this, "onInitContentType"]);
        }
        $descriptionAttribute = $this->descriptionAttribute;
        if (is_string($descriptionAttribute) && !empty($descriptionAttribute) && !isset($this->$descriptionAttribute)) {
            $this->on(static::$eventNewRecordCreated, [$this, 'onInitDescription']);
        }
        $this->on(static::EVENT_BEFORE_UPDATE, [$this, "onContentChanged"]);
        $this->initSelfBlameableEvents();
    }

    /**
     * @inheritdoc
     */
    public function enabledFields()
    {
        $fields = parent::enabledFields();
        if (is_string($this->createdByAttribute) && !empty($this->createdByAttribute)) {
            $fields[] = $this->createdByAttribute;
        }
        if (is_string($this->updatedByAttribute) && !empty($this->updatedByAttribute) &&
            $this->createdByAttribute != $this->updatedByAttribute) {
            $fields[] = $this->updatedByAttribute;
        }
        if (is_string($this->contentAttribute)) {
            $fields[] = $this->contentAttribute;
        }
        if (is_array($this->contentAttribute)) {
            $fields = array_merge($fields, $this->contentAttribute);
        }
        if (is_string($this->descriptionAttribute)) {
            $fields[] = $this->descriptionAttribute;
        }
        if (is_string($this->confirmationAttribute)) {
            $fields[] = $this->confirmationAttribute;
        }
        if (is_string($this->parentAttribute)) {
            $fields[] = $this->parentAttribute;
        }
        return $fields;
    }

    /**
     * Find all follows by specified identity. If `$identity` is null, the logged-in
     * identity will be taken.
     * @param string|integer $pageSize If it is 'all`, then will find all follows,
     * the `$currentPage` parameter will be skipped. If it is integer, it will be
     * regarded as sum of models in one page.
     * @param integer $currentPage The current page number, begun with 0.
     * @param mixed $identity It's type depends on {$this->hostClass}.
     * @return static[] If no follows, null will be given, or return follow array.
     */
    public static function findAllByIdentityInBatch($pageSize = 'all', $currentPage = 0, $identity = null)
    {
        if ($pageSize === 'all') {
            return static::findByIdentity($identity)->all();
        }
        return static::findByIdentity($identity)->page($pageSize, $currentPage)->all();
    }

    /**
     * Find one follow by specified identity. If `$identity` is null, the logged-in
     * identity will be taken. If $identity doesn't has the follower, null will
     * be given.
     * @param integer $id user id.
     * @param boolean $throwException
     * @param mixed $identity It's type depends on {$this->hostClass}.
     * @return static
     * @throws InvalidParamException
     */
    public static function findOneById($id, $throwException = true, $identity = null)
    {
        $query = static::findByIdentity($identity);
        if (!empty($id)) {
            $query = $query->id($id);
        }
        $model = $query->one();
        if (!$model && $throwException) {
            throw new InvalidParamException('Model Not Found.');
        }
        return $model;
    }

    /**
     * Get total of follows of specified identity.
     * @param mixed $identity It's type depends on {$this->hostClass}.
     * @return integer total.
     */
    public static function countByIdentity($identity = null)
    {
        return (int)(static::findByIdentity($identity)->count());
    }

    /**
     * Get pagination, used for building contents page by page.
     * @param integer $limit
     * @param mixed $identity It's type depends on {$this->hostClass}.
     * @return Pagination
     */
    public static function getPagination($limit = 10, $identity = null)
    {
        $limit = (int) $limit;
        $count = static::countByIdentity($identity);
        if ($limit > $count) {
            $limit = $count;
        }
        return new Pagination(['totalCount' => $count, 'pageSize' => $limit]);
    }
}
