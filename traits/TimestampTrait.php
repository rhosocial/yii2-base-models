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

use Closure;
use Throwable;
use yii\base\ModelEvent;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;
use yii\db\StaleObjectException;

/**
 * Entity features concerning timestamp.
 * @property-read array $timestampBehaviors
 * @property-read string|int createdAt
 * @property-read string|int updatedAt
 * @property-read array $createdAtRules
 * @property-read array $updatedAtRules
 * @property-read boolean isExpired
 * @property int|false expiredAfter the expiration duration in seconds, or false if not expired.
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
trait TimestampTrait
{
    /**
     * @var string|false Specifies the attribute name that records the creation time.
     * Set this attribute to false if you do not want to record it and know whether the entity has been edited.
     */
    public string|false $createdAtAttribute = 'created_at';

    /**
     * @var string|false Specifies the attribute name that records the last update time.
     * Set this attribute to false if you do not want to record it and know whether the entity has been edited.
     */
    public string|false $updatedAtAttribute = 'updated_at';

    /**
     * @var string|false This attribute determines when the current entity expires.
     * If not set, this function will not be enabled.
     */
    public string|false $expiredAfterAttribute = false;

    const TIME_FORMAT_DATETIME = 0;
    const TIME_FORMAT_TIMESTAMP = 1;
    const INIT_DATETIME = '1970-01-01 00:00:00';
    const INIT_TIMESTAMP = 0;
    const TIME_TYPE_UTC = 0;
    const TIME_TYPE_LOCAL = 1;

    /**
     * @var int Determine the format of timestamp.
     */
    public int $timeFormat = 0;
    public int $timeType = 0;

    /**
     * @var array|Closure|null
     */
    public array|Closure|null $expiredRemovingCallback = null;

    const EVENT_EXPIRED_REMOVED = 'expiredRemoved';

    /**
     * Check this entity whether expired.
     * This feature require creation time. If creation time didn't record, false
     * is returned.
     * This feature also need expiration duration. If expiration duration didn't
     * record, false is returned.
     * @return bool
     */
    public function getIsExpired(): bool
    {
        $createdAt = $this->getCreatedAt();
        if ($this->getExpiredAfter() === false || $createdAt === null) {
            return false;
        }
        if ($this->timeType == self::TIME_TYPE_LOCAL) {
            return $this->getDatetimeOffset($this->offsetDatetime($this->currentDatetime(), -$this->getExpiredAfter()), $createdAt) > 0;
        } elseif ($this->timeType == self::TIME_TYPE_UTC) {
            return $this->getDatetimeOffset($this->offsetDatetime($this->currentUtcDatetime(), -$this->getExpiredAfter()), $createdAt) > 0;
        }
        return false;
    }

    /**
     * Remove myself if expired.
     * The `expiredRemovingCallback` will be called before removing itself,
     * then it would trigger `self::EVENT_EXPIRED_REMOVED` event, and attach
     * the removing results.
     * @return bool
     * @throws Exception
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function removeIfExpired(): bool
    {
        if ($this->getIsExpired() && !$this->getIsNewRecord()) {
            if (($this->expiredRemovingCallback instanceof Closure || is_array($this->expiredRemovingCallback)) && is_callable($this->expiredRemovingCallback)) {
                call_user_func($this->expiredRemovingCallback, $this);
            }
            $result = $this->removeSelf();
            $this->trigger(self::EVENT_EXPIRED_REMOVED, new ModelEvent(['data' => ['result' => $result]]));
            return true;
        }
        return false;
    }

    /**
     * Remove self.
     * You can override this method for implementing more complex features.
     * @return int
     * @throws Throwable
     * @throws Exception
     * @throws StaleObjectException
     * @see delete()
     */
    public function removeSelf(): int
    {
        return $this->delete();
    }

    /**
     * We recommended you attach this event when after finding this active record.
     * @param ModelEvent $event
     * @return bool
     */
    public function onRemoveExpired($event): bool
    {
        return $event->sender->removeIfExpired();
    }

    /**
     * Get the current date & time in format of "Y-m-d H:i:s" or timestamp.
     * You can override this method to customize the return value.
     * @param ModelEvent $event
     * @return string|null Date & Time.
     */
    public static function getCurrentDatetime($event): ?string
    {
        $sender = $event->sender;
        /* @var $sender static */
        if ($sender->timeType == self::TIME_TYPE_UTC) {
            return $sender->currentUtcDatetime();
        } elseif ($sender->timeType == self::TIME_TYPE_LOCAL) {
            return $sender->currentDatetime();
        }
        return null;
    }

    /**
     * Get current date & time, by current time format.
     * @return int|string|null Date & time string if format is datetime, or timestamp.
     */
    public function currentDatetime(): int|string|null
    {
        if ($this->timeFormat === self::TIME_FORMAT_DATETIME) {
            return date('Y-m-d H:i:s');
        }
        if ($this->timeFormat === self::TIME_FORMAT_TIMESTAMP) {
            return time();
        }
        return null;
    }

    /**
     * Get current Greenwich date & time (UTC), by current time format.
     * @return int|string|null Date & time string if format is datetime, or timestamp.
     */
    public function currentUtcDatetime(): int|string|null
    {
        if ($this->timeFormat === self::TIME_FORMAT_DATETIME) {
            return gmdate('Y-m-d H:i:s');
        }
        if ($this->timeFormat === self::TIME_FORMAT_TIMESTAMP) {
            return time();
        }
        return null;
    }

    /**
     * Get offset date & time, by current time format.
     * @param string|int|null $time Date &time string or timestamp.
     * @param int $offset Offset in seconds.
     * @return int|string|null Date & time string if format is datetime, or timestamp.
     */
    public function offsetDatetime(string|int|null $time = null, int $offset = 0): int|string|null
    {
        if ($this->timeFormat === self::TIME_FORMAT_DATETIME) {
            return date('Y-m-d H:i:s', strtotime(($offset >= 0 ? "+$offset" : $offset) . " seconds", is_string($time) ? strtotime($time) : (is_int($time) ? $time : time())));
        }
        if ($this->timeFormat === self::TIME_FORMAT_TIMESTAMP) {
            return (is_int($time) ? $time : time()) + $offset;
        }
        return null;
    }

    /**
     * Calculate the time difference(in seconds).
     * @param string|int $datetime1
     * @param string|int|null $datetime2 If this parameter is not specified, the current time is used.
     * @return int|string|null Positive integer if $datetime1 is later than $datetime2, and vise versa.
     */
    public function getDatetimeOffset(string|int $datetime1, string|int|null $datetime2 = null): int|string|null
    {
        if ($datetime2 === null) {
            if ($this->timeType == self::TIME_TYPE_LOCAL) {
                $datetime2 = $this->currentDatetime();
            } elseif ($this->timeType == self::TIME_TYPE_UTC) {
                $datetime2 = $this->currentUtcDatetime();
            }
        }
        if ($this->timeFormat == self::TIME_FORMAT_DATETIME) {
            $datetime1 = strtotime($datetime1);
            $datetime2 = strtotime($datetime2);
        }
        return $datetime1 - $datetime2;
    }

    /**
     * Get init date & time in format of "Y-m-d H:i:s" or timestamp.
     * @param ModelEvent $event
     * @return int|string|null
     */
    public static function getInitDatetime($event): int|string|null
    {
        $sender = $event->sender;
        /* @var $sender static */
        return $sender->initDatetime();
    }

    /**
     * Get init date & time, by current time format.
     * @return int|string|null Date & time string if format is datetime, or timestamp.
     */
    public function initDatetime(): int|string|null
    {
        if ($this->timeFormat === self::TIME_FORMAT_DATETIME) {
            return self::INIT_DATETIME;
        }
        if ($this->timeFormat === self::TIME_FORMAT_TIMESTAMP) {
            return self::INIT_TIMESTAMP;
        }
        return null;
    }

    /**
     * Check whether the attribute is init datetime.
     * @param mixed $attribute
     * @return bool
     */
    protected function isInitDatetime(mixed $attribute): bool
    {
        if ($this->timeFormat === self::TIME_FORMAT_DATETIME) {
            return $attribute == self::INIT_DATETIME || $attribute == null;
        }
        if ($this->timeFormat === self::TIME_FORMAT_TIMESTAMP) {
            return $attribute == self::INIT_TIMESTAMP || $attribute == null;
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
    public function onUpdateCurrentDatetime($event): string
    {
        return static::getCurrentDatetime($event);
    }

    /**
     * Behaviors associated with timestamp.
     * @return array behaviors
     */
    public function getTimestampBehaviors(): array
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
     * Get the creation time.
     * If the property name of the record creation time [[$createdAtAttribute]] is not specified, null is returned.
     * @return string|null timestamp
     */
    public function getCreatedAt(): ?string
    {
        $createdAtAttribute = $this->createdAtAttribute;
        if (!is_string($createdAtAttribute) || empty($createdAtAttribute)) {
            return null;
        }
        return $this->$createdAtAttribute;
    }

    /**
     * Get rules associated with createdAtAttribute.
     * The default rule is safe. Because the [[TimestampBehavior]] will attach
     * the creation time automatically.
     * Under normal circumstances is not recommended to amend.
     * If `createdAtAttribute` is not specified, the empty array will be given.
     * @return array rules
     */
    public function getCreatedAtRules(): array
    {
        if (!is_string($this->createdAtAttribute) || empty($this->createdAtAttribute)) {
            return [];
        }
        return [
            [[$this->createdAtAttribute], 'safe'],
        ];
    }

    /**
     * Get the last update time.
     * If the property name of the record last update time [[$updatedAtAttribute]] is not specified, null is returned.
     * @return string|null timestamp
     */
    public function getUpdatedAt(): ?string
    {
        $updatedAtAttribute = $this->updatedAtAttribute;
        if (!is_string($updatedAtAttribute) || empty($updatedAtAttribute)) {
            return null;
        }
        return $this->$updatedAtAttribute;
    }

    /**
     * Get rules associated with `updatedAtAttribute`.
     * The default rule is safe. Because the [[TimestampBehavior]] will attach
     * the last update time automatically.
     * Under normal circumstances is not recommended to amend.
     * If `updatedAtAttribute` is not specified, the empty array will be given.
     * @return array rules
     */
    public function getUpdatedAtRules(): array
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
     * If `expiredAfterAttribute` is not specified, false will be given.
     * @return false|int
     */
    public function getExpiredAfter(): false|int
    {
        if (!is_string($this->expiredAfterAttribute) || empty($this->expiredAfterAttribute)) {
            return false;
        }
        return (int)($this->{$this->expiredAfterAttribute});
    }

    /**
     * Set expiration duration (in seconds).
     * If [[$expiredAfterAttribute]] is not specified, this feature will be skipped,
     * and return false.
     * @param int $expiredAfter the duration after which is expired (in seconds).
     * @return bool|int
     */
    public function setExpiredAfter(int $expiredAfter): bool|int
    {
        if (!is_string($this->expiredAfterAttribute) || empty($this->expiredAfterAttribute)) {
            return false;
        }
        return $this->{$this->expiredAfterAttribute} = $expiredAfter;
    }

    /**
     * Get rules associated with `expiredAfterAttribute`.
     * The default rule is unsigned integer.
     * Under normal circumstances is not recommended to amend.
     * If `expiredAfterAttribute` is not specified, the empty array will be given.
     * @return array The key of array is not specified.
     */
    public function getExpiredAfterRules(): array
    {
        if (!is_string($this->expiredAfterAttribute) || empty($this->expiredAfterAttribute)) {
            return [];
        }
        return [
            [[$this->expiredAfterAttribute], 'integer', 'min' => 0],
        ];
    }

    /**
     * Get enabled fields associated with timestamp, including `createdAtAttribute`,
     * `updatedAtAttribute` and `expiredAfterAttribute`.
     * @return array field list. The keys of array are not specified.
     */
    public function enabledTimestampFields(): array
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

    /**
     * Check to see if the entity has ever been edited.
     *
     * The specific judgment rules are:
     *     Check whether the "creation time" and "last update time" are consistent.
     *     If one of the two does not exist or is inconsistent, false is returned.
     *     Returns true if they exist and are consistent.
     * You can override this method to implement more complex function.
     * @return bool Whether this entity has ever been edited.
     */
    public function hasEverBeenEdited(): bool
    {
        if ($this->getCreatedAt() === null || $this->getUpdatedAt() === null) {
            return false;
        }
        return $this->getCreatedAt() !== $this->getUpdatedAt();
    }
}
