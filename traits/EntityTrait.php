<?php

/**
 *   _   __ __ _____ _____ ___  ____  _____
 *  | | / // // ___//_  _//   ||  __||_   _|
 *  | |/ // /(__  )  / / / /| || |     | |
 *  |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2023 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\traits;

use Yii;
use yii\base\ModelEvent;
use yii\caching\Cache;
use yii\caching\TagDependency;

/**
 * This trait must be used in class extended from ActiveRecord. The ActiveRecord
 * supports [[\yii\db\ActiveRecord]], [[\yii\mongodb\ActiveRecord]], [[\yii\redis\ActiveRecord]].
 * @property array $entityRules
 * @property array $entityBehaviors
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
trait EntityTrait
{
    use GUIDTrait, IDTrait, IPTrait, TimestampTrait, SubsidiaryTrait;
    
    private array|false|null $entityLocalRules = [];
    private array|false|null $entityLocalBehaviors = [];

    /**
     * @var string cache key and tag prefix. the prefix is usually set to full
     * qualified class name.
     */
    public string $cachePrefix = '';

    const EVENT_NEW_RECORD_CREATED = 'newRecordCreated';

    public static string $cacheKeyEntityRules = 'entity_rules';
    public static string $cacheTagEntityRules = 'tag_entity_rules';
    public static string $cacheKeyEntityBehaviors = 'entity_behaviors';
    public static string $cacheTagEntityBehaviors = 'tag_entity_behaviors';
    
    /**
     * @var string cache component id.
     */
    public string $cacheId = 'cache';
    
    /**
     * @var bool Determines to skip initialization.
     */
    public bool $skipInit = false;
    
    /**
     * @var ?string the name of query class or subclass.
     */
    public ?string $queryClass = null;
    
    /**
     * @return static New self without any initializations.
     */
    public static function buildNoInitModel(): static
    {
        return new static(['skipInit' => true]);
    }
    
    /**
     * Populate and return the entity rules.
     * You should call this function in your extended class and merge the result
     * with your rules, instead of overriding it, unless you know the
     * consequences.
     * The classical rules are like following:
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
        return $this->getEntityRules();
    }
    
    /**
     * Populate and return the entity behaviors.
     * You should call this function in your extended class and merge the result
     * with your behaviors, instead of overriding it, unless you know the
     * consequences.
     * @return array
     */
    public function behaviors()
    {
        return $this->getEntityBehaviors();
    }
    
    /**
     * Get cache component. If cache component is not configured, Yii::$app->cache
     * will be given.
     * @return Cache cache component.
     */
    protected function getCache()
    {
        $cacheId = $this->cacheId;
        return empty($cacheId) ? Yii::$app->cache : Yii::$app->$cacheId;
    }
    
    /**
     * Get entity rules cache key.
     * @return string cache key.
     */
    public function getEntityRulesCacheKey()
    {
        return static::class . $this->cachePrefix . static::$cacheKeyEntityRules;
    }
    
    /**
     * Get entity rules cache tag.
     * @return string cache tag.
     */
    public function getEntityRulesCacheTag()
    {
        return static::class . $this->cachePrefix . static::$cacheTagEntityRules;
    }
    
    /**
     * Get entity rules.
     * @return array rules.
     */
    public function getEntityRules()
    {
        $cache = $this->getCache();
        if ($cache) {
            $this->entityLocalRules = $cache->get($this->getEntityRulesCacheKey());
        }
        if (empty($this->entityLocalRules) || !is_array($this->entityLocalRules)) {
            $rules = array_merge($this->getGuidRules(), $this->getIdRules(), $this->getCreatedAtRules(), $this->getUpdatedAtRules(), $this->getExpiredAfterRules(), $this->getIpRules());
            $this->setEntityRules($rules);
        }
        return $this->entityLocalRules;
    }
    
    /**
     * Set entity rules.
     * @param array $rules
     */
    protected function setEntityRules($rules = [])
    {
        $this->entityLocalRules = $rules;
        $cache = $this->getCache();
        if ($cache) {
            $tagDependency = new TagDependency(
                ['tags' => [$this->getEntityRulesCacheTag()]]
            );
            $cache->set($this->getEntityRulesCacheKey(), $rules, 0, $tagDependency);
        }
    }
    
    /**
     * Get entity behaviors cache key.
     * @return string cache key.
     */
    public function getEntityBehaviorsCacheKey()
    {
        return static::class . $this->cachePrefix . static::$cacheKeyEntityBehaviors;
    }
    
    /**
     * Get entity behaviors cache tag.
     * @return string cache tag.
     */
    public function getEntityBehaviorsCacheTag()
    {
        return static::class . $this->cachePrefix . static::$cacheTagEntityBehaviors;
    }
    
    /**
     * Get the entity behaviors.
     * @return array
     */
    public function getEntityBehaviors()
    {
        $cache = $this->getCache();
        if ($cache) {
            $this->entityLocalBehaviors = $cache->get($this->getEntityBehaviorsCacheKey());
        }
        if (empty($this->entityLocalBehaviors) || !is_array($this->entityLocalBehaviors)) {
            $this->setEntityBehaviors($this->getTimestampBehaviors());
        }
        return $this->entityLocalBehaviors;
    }
    
    /**
     * Set the entity behaviors.
     * @param array $behaviors
     */
    protected function setEntityBehaviors($behaviors)
    {
        $this->entityLocalBehaviors = $behaviors;
        $cache = $this->getCache();
        if ($cache) {
            $tagDependencyConfig = ['tags' => [$this->getEntityBehaviorsCacheTag()]];
            $tagDependency = new TagDependency($tagDependencyConfig);
            $cache->set($this->getEntityBehaviorsCacheKey(), $behaviors, 0, $tagDependency);
        }
    }
    
    /**
     * Reset cache key.
     * @param string $cacheKey
     * @param mixed $value
     * @return boolean whether the value is successfully stored into cache. if
     * cache component was not configured, then return false directly.
     */
    public function resetCacheKey($cacheKey, $value = false): bool
    {
        $cache = $this->getCache();
        if ($cache) {
            return $this->getCache()->set($cacheKey, $value);
        }
        return false;
    }
    
    /**
     * Attach events associated with entity model.
     */
    protected function initEntityEvents(): void
    {
        $this->on(static::EVENT_INIT, [$this, 'onInitCache']);
        $this->attachInitGUIDEvent(static::EVENT_NEW_RECORD_CREATED);
        $this->attachInitIDEvent(static::EVENT_NEW_RECORD_CREATED);
        $this->attachInitIPEvent(static::EVENT_NEW_RECORD_CREATED);
        if ($this->isNewRecord) {
            $this->trigger(static::EVENT_NEW_RECORD_CREATED);
        }
        $this->on(static::EVENT_AFTER_FIND, [$this, 'onRemoveExpired']);
    }
    
    /**
     * Initialize the cache prefix.
     * @param ModelEvent $event
     */
    public function onInitCache($event): void
    {
        $sender = $event->sender;
        $data = $event->data;
        if (isset($data['prefix'])) {
            $sender->cachePrefix = $data['prefix'];
        } else {
            $sender->cachePrefix = $sender::className();
        }
    }
    
    /**
     * Record warnings.
     */
    protected function recordWarnings(): void
    {
        if (YII_ENV != YII_ENV_PROD || YII_DEBUG) {
            Yii::warning($this->errors);
        }
    }
    
    /**
     * Get guid or id. if neither disabled, return null.
     * @return string
     */
    public function __toString()
    {
        if (is_string($this->guidAttribute) && !empty($this->guidAttribute)) {
            return $this->getGUID();
        }
        if (is_string($this->idAttribute) && !empty($this->idAttribute)) {
            return $this->getId();
        }
        return parent::__toString();
    }
    
    /**
     * @inheritdoc
     * -------------
     * if enable `$idAttribute` and $row[$idAttribute] set, the `idPreassigned`
     * will be assigned to true.
     */
    public static function instantiate($row): static
    {
        $self = static::buildNoInitModel();
        if (isset($self->idAttribute) && isset($row[$self->idAttribute])) {
            $model = new static(['idPreassigned' => true]);
        } else {
            $model = new static;
        }
        return $model;
    }
    
    /**
     * unset entity attributes.
     * @return array result.
     */
    public function unsetSelfFields(): array
    {
        return static::unsetFields($this->attributes, $this->enabledFields());
    }
    
    /**
     * unset fields of array.
     * @param array|null $array
     * @param array|null $fields
     * @return array
     */
    public static function unsetFields(array $array = null, array $fields = null): array
    {
        if (!is_array($array)) {
            $fields = [];
        }
        foreach ($array as $key => $value) {
            if (is_string($key) && in_array($key, $fields)) {
                unset($array[$key]);
            }
        }
        return $array;
    }
    
    /**
     * Get enabled fields.
     * @return string[]
     */
    public function enabledFields(): array
    {
        return array_merge(
            (is_string($this->guidAttribute) && !empty($this->guidAttribute)) ? [$this->guidAttribute] : [],
            (is_string($this->idAttribute) && !empty($this->idAttribute)) ? [$this->idAttribute] : [],
            $this->enabledTimestampFields(),
            $this->enabledIPFields()
        );
    }
}
