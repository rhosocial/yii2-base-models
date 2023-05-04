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

use rhosocial\base\helpers\Number;
use rhosocial\base\models\traits\config\GUIDConfig;
use yii\base\ModelEvent;

/**
 * Entity features concerning GUID.
 * @property string $GUID GUID value in 128-bit(16 bytes) binary format.
 * @property-read string $readableGUID Readable GUID value seperated with four hyphens.
 * @property-read array $guidRules
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
trait GUIDTrait
{
    
    /**
     * @var string|false REQUIRED. The attribute that will receive the GUID value.
     */
    public string|false $guidAttribute = 'guid';
    
    /**
     * DO NOT MODIFY OR OVERRIDE THIS METHOD UNLESS YOU KNOW THE CONSEQUENCES.
     * @return string
     */
    public function getReadableGuidAttribute(): string
    {
        return 'readableGuid';
    }
    
    /**
     * Attach `onInitGUIDAttribute` event.
     * @param string $eventName
     */
    protected function attachInitGUIDEvent(string $eventName): void
    {
        $this->on($eventName, [$this, 'onInitGUIDAttribute']);
    }
    
    /**
     * Initialize the GUID attribute with new generated GUID.
     * This method is ONLY used for being triggered by event. DO NOT call,
     * override or modify it directly, unless you know the consequences.
     * @param ModelEvent $event
     */
    public function onInitGUIDAttribute(mixed $event): void
    {
        $sender = $event->sender;
        /* @var $sender static */
        $sender->setGUID(static::generateGuid());
    }

    /**
     * Generate GUID in binary.
     * @return string GUID.
     */
    public static function generateGuid(): string
    {
        return Number::guid();
    }

    /**
     * Check if the $guid existed in current database table.
     * @param string $guid the GUID to be checked.
     * @return boolean Whether the $guid exists or not.
     */
    public static function checkGuidExists(string $guid): bool
    {
        return static::findOne($guid) !== null;
    }
    
    /**
     * Get the rules associated with GUID attribute.
     * @return array GUID rules.
     */
    public function getGUIDRules(): array
    {
        $rules = [];
        if (!empty($this->guidAttribute)) {
            $rules = [
                [[$this->guidAttribute], 'required',],
                [[$this->guidAttribute], 'unique',],
                [[$this->guidAttribute], 'string', 'max' => 36],
            ];
        }
        return $rules;
    }

    /**
     * Get GUID, in spite of guid attribute name.
     * @return string|null
     */
    public function getGUID(): ?string
    {
        $guidAttribute = $this->guidAttribute;
        return (!empty($guidAttribute)) ? $this->$guidAttribute : null;
    }
    
    /**
     * Get Readable GUID.
     * @return string
     */
    public function getReadableGUID(): string
    {
        $guid = $this->getGUID();
        if (preg_match(Number::GUID_REGEX, $guid)) {
            return $guid;
        }
        return Number::guid(false, false, $guid);
    }

    /**
     * Set guid, in spite of guid attribute name.
     * @param string $guid
     * @return string|null
     */
    public function setGUID(string $guid): ?string
    {
        $guidAttribute = $this->guidAttribute;
        if (empty($guidAttribute)) {
            return null;
        }
        /**
        if (preg_match(Number::GUID_REGEX, $guid)) {
            $guid = hex2bin(str_replace(['{', '}', '-'], '', $guid));
        }*/
        if (strlen($guid) == 16) {
            $guid = Number::guid(false, false, $guid);
        }
        return $this->$guidAttribute = $guid;
    }

    /**
     * Composite GUIDs from models.
     * @param mixed $models
     * @return array|string|null
     */
    public static function compositeGUIDs(mixed $models): array|string|null
    {
        if (empty($models)) {
            return null;
        }
        if (!is_array($models) && $models instanceof static) {
            return $models->getGUID();
        }
        if (is_string($models) && preg_match(Number::GUID_REGEX, $models)) {
            return $models;
        }
        $guids = [];
        foreach ($models as $model) {
            if ($model instanceof static) {
                $guids[] = $model->getGUID();
            } elseif (is_string($model)) {
                if (preg_match(Number::GUID_REGEX, $model)) {
                    $guids[] = $model;
                } elseif (strlen($model) == 16) {
                    $guids[] = Number::guid(false, false, $model);
                }
            }
        }
        return $guids;
    }

    /**
     * Composited guid string chunk into one string.
     * @param array $guids
     * @return string
     */
    public static function composite_guid_strs($guids): string
    {
        if (!is_array($guids) || empty($guids)) {
            return '';
        }
        if (is_string($guids[0]) && strlen($guids[0]) == 36) {
            return implode('', $guids);
        }
        $validGuids = Number::unsetInvalidGUIDs($guids);
        return implode('', $validGuids);
    }

    /**
     * Divide composited guid binary into chunk.
     * @param string $guids
     * @return array
     */
    public static function divide_guid_strs($guids): array
    {
        if (!is_string($guids) || strlen($guids) == 0 || strlen($guids) % 36 > 0) {
            return [];
        }
        return str_split($guids, 36);
    }

    protected function applyGUIDConfig(?GUIDConfig $guidConfig): void
    {
        if ($guidConfig == null) {
            return;
        }
        $this->guidAttribute = $guidConfig->guidAttribute;
    }
}