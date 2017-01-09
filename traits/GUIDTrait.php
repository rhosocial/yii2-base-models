<?php

/**
 *   _   __ __ _____ _____ ___  ____  _____
 *  | | / // // ___//_  _//   ||  __||_   _|
 *  | |/ // /(__  )  / / / /| || |     | |
 *  |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\traits;

use rhosocial\base\helpers\Number;
use yii\base\ModelEvent;

/**
 * Entity features concerning GUID.
 * @property string $GUID GUID value in 128-bit(16 bytes) binary format.
 * @property-read string $readableGUID Readable GUID value seperated with four hyphens.
 * @property-read array $guidRules
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
trait GUIDTrait
{
    
    /**
     * @var string REQUIRED. The attribute that will receive the GUID value.
     */
    public $guidAttribute = 'guid';
    
    /**
     * DO NOT MODIFY OR OVERRIDE THIS METHOD UNLESS YOU KNOW THE CONSEQUENCES.
     * @return string
     */
    public function getReadableGuidAttribute()
    {
        return 'readableGuid';
    }
    
    /**
     * Attach `onInitGUIDAttribute` event.
     * @param string $eventName
     */
    protected function attachInitGUIDEvent($eventName)
    {
        $this->on($eventName, [$this, 'onInitGUIDAttribute']);
    }
    
    /**
     * Initialize the GUID attribute with new generated GUID.
     * This method is ONLY used for being triggered by event. DO NOT call,
     * ovveride or modify it directly, unless you know the conquences.
     * @param ModelEvent $event
     */
    public function onInitGUIDAttribute($event)
    {
        $sender = $event->sender;
        /* @var $sender static */
        $sender->setGUID(static::generateGuid());
    }

    /**
     * Generate GUID in binary.
     * @return string GUID.
     */
    public static function generateGuid()
    {
        return Number::guid_bin();
    }

    /**
     * Check if the $guid existed in current database table.
     * @param string $guid the GUID to be checked.
     * @return boolean Whether the $guid exists or not.
     */
    public static function checkGuidExists($guid)
    {
        return (self::findOne($guid) !== null);
    }
    
    /**
     * Get the rules associated with GUID attribute.
     * @return array GUID rules.
     */
    public function getGUIDRules()
    {
        $rules = [];
        if (is_string($this->guidAttribute) && !empty($this->guidAttribute)) {
            $rules = [
                [[$this->guidAttribute], 'required',],
                [[$this->guidAttribute], 'unique',],
                [[$this->guidAttribute], 'string', 'max' => 16],
            ];
        }
        return $rules;
    }
    
    /**
     * Get GUID, in spite of guid attribute name.
     * @return string
     */
    public function getGUID()
    {
        $guidAttribute = $this->guidAttribute;
        return (is_string($guidAttribute) && !empty($guidAttribute)) ? $this->$guidAttribute : null;
    }
    
    /**
     * Get Readable GUID.
     * @return string
     */
    public function getReadableGUID()
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
     * @return string
     */
    public function setGUID($guid)
    {
        $guidAttribute = $this->guidAttribute;
        if (preg_match(Number::GUID_REGEX, $guid)) {
            $guid = hex2bin(str_replace(['{', '}', '-'], '', $guid));
        }
        return (is_string($guidAttribute) && !empty($guidAttribute)) ? $this->$guidAttribute = $guid : null;
    }
}