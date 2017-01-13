<?php

/**
 *   _   __ __ _____ _____ ___  ____  _____
 *  | | / // // ___//_  _//   ||  __||_   _|
 *  | |/ // /(__  )  / / / /| || |     | |
 *  |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2017 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\traits;

use Closure;
use yii\base\ModelEvent;
use yii\behaviors\TimestampBehavior;

/**
 * Entity features concerning timestamp.
 * @property-read array $timestampBehaviors
 * @property-read string|int createdAt
 * @property-read string|int updatedAt
 * @property-read array $createdAtRules
 * @property-read array $updatedAtRules
 * @property-read boolean isExpired
 * @property int|false expiredAfter the expiration duration in seconds, or false if not expired.
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
trait TimestampTrait
{
    /**
     * @var string|false the attribute that receive datetime value
     * Set this property to false if you do not want to record the creation time.
     */
    public $createdAtAttribute = 'created_at';
    
    /**
     * @var string|false the attribute that receive datetime value.
     * Set this property to false if you do not want to record the update time.
     */
    public $updatedAtAttribute = 'updated_at';
    
    public $expiredAfterAttribute = false;

    /**
     * @var integer Determine the format of timestamp.
     */
    public $timeFormat = 0;
    public static $timeFormatDatetime = 0;
    public static $timeFormatTimestamp = 1;
    public static $initDatetime = '1970-01-01 00:00:00';
    public static $initTimestamp = 0;
    /**
     * @var Closure
     */
    public $expiredRemovingCallback;
    public static $eventExpiredRemoved = 'expiredRemoved';
    
    /**
     * Check this entity whether expired.
     * @return boolean
     */
    public function getIsExpired()
    {
        $createdAt = $this->getCreatedAt();
        if ($this->getExpiredAfter() === false || $createdAt === null) {
            return false;
        }
        return $this->offsetDatetime($this->currentDatetime(), -$this->getExpiredAfter()) > $createdAt;
    }
    
    /**
     * Remove myself if expired.
     * @return boolean
     */
    public function removeIfExpired()
    {
        if ($this->getIsExpired() && !$this->getIsNewRecord()) {
            if (($this->expiredRemovingCallback instanceof Closure || is_array($this->expiredRemovingCallback)) && is_callable($this->expiredRemovingCallback)) {
                $result = call_user_func($this->expiredRemovingCallback, $this);
            }
            $result = $this->removeSelf();
            $this->trigger(static::$eventExpiredRemoved, new ModelEvent(['data' => ['result' => $result]]));
        }
        return false;
    }
    
    /**
     * Remove self.
     * You can override this method for implementing more complex features.
     * @see delete()
     * @return integer
     */
    public function removeSelf()
    {
        return $this->delete();
    }
    
    /**
     * We recommened you attach this event when after finding this active record.
     * @param ModelEvent $event
     * @return boolean
     */
    public function onRemoveExpired($event)
    {
        return $event->sender->removeIfExpired();
    }
    
    /**
     * Get the current date & time in format of "Y-m-d H:i:s" or timestamp.
     * You can override this method to customize the return value.
     * @param ModelEvent $event
     * @return string Date & Time.
     */
    public static function getCurrentDatetime($event)
    {
        $sender = $event->sender;
        return $sender->currentDatetime();
    }
    
    /**
     * Get current date & time, by current time format.
     * @return string|int Date & time string if format is datetime, or timestamp.
     */
    public function currentDatetime()
    {
        if ($this->timeFormat === self::$timeFormatDatetime) {
            return date('Y-m-d H:i:s');
        }
        if ($this->timeFormat === self::$timeFormatTimestamp) {
            return time();
        }
        return null;
    }
    
    /**
     * Get offset date & time, by current time format.
     * @param string|int $time Date &time string or timestamp.
     * @param int $offset Offset in seconds.
     * @return string|int Date & time string if format is datetime, or timestamp.
     */
    public function offsetDatetime($time = null, $offset = 0)
    {
        if ($this->timeFormat === self::$timeFormatDatetime) {
            return date('Y-m-d H:i:s', strtotime(($offset >= 0 ? "+$offset" : $offset) . " seconds", is_string($time) ? strtotime($time) : (is_int($time) ? $time : time())));
        }
        if ($this->timeFormat === self::$timeFormatTimestamp) {
            return (is_int($time) ? $time : time()) + $offset;
        }
        return null;
    }
    
    /**
     * Get init date & time in format of "Y-m-d H:i:s" or timestamp.
     * @param ModelEvent $event
     * @return string|int
     */
    public static function getInitDatetime($event)
    {
        $sender = $event->sender;
        return $sender->initDatetime();
    }
    
    /**
     * Get init date & time, by current time format.
     * @return string|int Date & time string if format is datetime, or timestamp.
     */
    public function initDatetime()
    {
        if ($this->timeFormat === self::$timeFormatDatetime) {
            return static::$initDatetime;
        }
        if ($this->timeFormat === self::$timeFormatTimestamp) {
            return static::$initTimestamp;
        }
        return null;
    }
    
    /**
     * Check whether the attribute is init datetime.
     * @param mixed $attribute
     * @return boolean
     */
    protected function isInitDatetime($attribute)
    {
        if ($this->timeFormat === self::$timeFormatDatetime) {
            return $attribute == static::$initDatetime || $attribute == null;
        }
        if ($this->timeFormat === self::$timeFormatTimestamp) {
            return $attribute == static::$initTimestamp || $attribute == null;
        }
        return false;
    }
    
    /**
     * Get the current date & time in format of "Y-m-d H:i:s".
     * This method is ONLY used for being triggered by event. DO NOT call,
     * override or modify it directly, unless you know the consequences.
     * @param ModelEvent $event
     * @return string Date & Time.
     */
    public function onUpdateCurrentDatetime($event)
    {
        return self::getCurrentDatetime($event);
    }
    
    /**
     * Behaviors associated with timestamp.
     * @return array behaviors
     */
    public function getTimestampBehaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => $this->createdAtAttribute,
                'updatedAtAttribute' => $this->updatedAtAttribute,
                'value' => [$this, 'onUpdateCurrentDatetime'],
            ]
        ];
    }
    
    /**
     * Get creation time.
     * @return string timestamp
     */
    public function getCreatedAt()
    {
        $createdAtAttribute = $this->createdAtAttribute;
        if (!is_string($createdAtAttribute) || empty($createdAtAttribute)) {
            return null;
        }
        return $this->$createdAtAttribute;
    }
    
    /**
     * Get rules associated with createdAtAttribute.
     * @return array rules
     */
    public function getCreatedAtRules()
    {
        if (!is_string($this->createdAtAttribute) || empty($this->createdAtAttribute)) {
            return [];
        }
        return [
            [[$this->createdAtAttribute], 'safe'],
        ];
    }
    
    /**
     * Get update time.
     * @return string timestamp
     */
    public function getUpdatedAt()
    {
        $updatedAtAttribute = $this->updatedAtAttribute;
        if (!is_string($updatedAtAttribute) || empty($updatedAtAttribute)) {
            return null;
        }
        return $this->$updatedAtAttribute;
    }
    
    /**
     * Get rules associated with `updatedAtAttribute`.
     * @return array rules
     */
    public function getUpdatedAtRules()
    {
        if (!is_string($this->updatedAtAttribute) || empty ($this->updatedAtAttribute)) {
            return [];
        }
        return [
            [[$this->updatedAtAttribute], 'safe'],
        ];
    }
    
    /**
     * Get expiration duration.
     * @return boolean
     */
    public function getExpiredAfter()
    {
        if (!is_string($this->expiredAfterAttribute) || empty($this->expiredAfterAttribute)) {
            return false;
        }
        return (int)($this->{$this->expiredAfterAttribute});
    }
    
    /**
     * Set expiration duration.
     * @param integer $expiredAfter
     * @return boolean|integer
     */
    public function setExpiredAfter($expiredAfter)
    {
        if (!is_string($this->expiredAfterAttribute) || empty($this->expiredAfterAttribute)) {
            return false;
        }
        return (int)($this->{$this->expiredAfterAttribute} = (int)$expiredAfter);
    }
    
    /**
     * Get rules associated with `expiredAfterAttribute`.
     * @return array
     */
    public function getExpiredAfterRules()
    {
        if (!is_string($this->expiredAfterAttribute) || empty($this->expiredAfterAttribute)) {
            return [];
        }
        return [
            [[$this->expiredAfterAttribute], 'integer', 'min' => 0],
        ];
    }
    
    /**
     *
     * @return array
     */
    public function enabledTimestampFields()
    {
        $fields = [];
        if (is_string($this->createdAtAttribute) && !empty($this->createdAtAttribute)) {
            $fields[] = $this->createdAtAttribute;
        }
        if (is_string($this->updatedAtAttribute) && !empty($this->updatedAtAttribute)) {
            $fields[] = $this->updatedAtAttribute;
        }
        if (is_string($this->expiredAfterAttribute) && !empty($this->expiredAfterAttribute)) {
            $fields[] = $this->expiredAfterAttribute;
        }
        return $fields;
    }
}
