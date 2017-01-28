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

namespace rhosocial\base\models\models;

use MongoDB\BSON\Binary;
use MongoDB\BSON\ObjectID;
use rhosocial\base\helpers\Number;
use rhosocial\base\helpers\IP;
use rhosocial\base\models\queries\BaseMongoEntityQuery;
use rhosocial\base\models\traits\EntityTrait;
use yii\mongodb\ActiveRecord;

/**
 * Description of BaseMongoEntityModel
 *
 * @property string $GUID GUID value in readable format (same as $readableGUID).
 * @property ObjectID $ID
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
abstract class BaseMongoEntityModel extends ActiveRecord
{
    use EntityTrait;
    
    public function getGUIDRules()
    {
        $rules = [];
        if (is_string($this->guidAttribute) || !empty($this->guidAttribute)) {
            $rules = [
                [[$this->guidAttribute], 'required',],
            ];
        }
        return $rules;
    }
    
    /**
     * Get GUID only for referencing, avoiding conflict with 'guid' field.
     * @return 
     */
    public function getRefGUID()
    {
        return $this->getGUID();
    }
    
    public function getGUID()
    {
        $guidAttribute = $this->guidAttribute;
        return (!is_string($guidAttribute) || empty($guidAttribute)) ? null : $this->$guidAttribute->getData();
    }
    
    public function setGUID($guid)
    {
        $guidAttribute = $this->guidAttribute;
        if (!is_string($guidAttribute) || empty($guidAttribute)) {
            return null;
        }
        if (preg_match(Number::GUID_REGEX, $guid)) {
            $guid = hex2bin(str_replace(['{', '}', '-'], '', $guid));
        }
        return $this->$guidAttribute = new Binary($guid, Binary::TYPE_UUID);
    }
    
    /**
     * Check if the $guid existed in current database table.
     * @param string|Binary $guid the GUID to be checked.
     * @return boolean Whether the $guid exists or not.
     */
    public static function checkGuidExists($guid)
    {
        if (is_string($guid)) {
            if (strlen($guid) == 16) {
                $binary = new Binary($guid, Binary::TYPE_UUID);
            } elseif (preg_match(Number::GUID_REGEX, $guid)) {
                $binary = new Binary(Number::guid_bin($guid), Binary::TYPE_UUID);
            } else {
                return false;
            }
            return static::findOne($binary) !== null;
        }
        if ($guid instanceof Binary) {
            return static::findOne($guid) !== null;
        }
        return false;
    }
    
    /**
     * Get the rules associated with ip attributes.
     * @return array
     */
    public function getIPRules()
    {
        $rules = [];
        if ($this->enableIP & static::$ipv4) {
            $rules = [
                [[$this->ipAttribute],
                    'string'
                ],
            ];
        }
        if ($this->enableIP & static::$ipv6) {
            $rules = [
                [[$this->ipAttribute],
                    'string'
                ],
            ];
        }
        if ($this->enableIP & static::$ipAll) {
            $rules[] = [
                [$this->ipTypeAttribute], 'in', 'range' => [IP::IPv4, IP::IPv6],
            ];
        }
        return $rules;
    }
    
    /**
     * Get the IPv4 address.
     * @return string
     */
    protected function getIPv4Address()
    {
        return ($this->{$this->ipAttribute});
    }
    
    /**
     * Get the IPv6 address.
     * @return string
     */
    protected function getIPv6Address()
    {
        return ($this->{$this->ipAttribute});
    }
    
    protected function setIPv4Address($ipAddress)
    {
        return $this->{$this->ipAttribute} = ($ipAddress);
    }
    
    protected function setIPv6Address($ipAddress)
    {
        return $this->{$this->ipAttribute} = ($ipAddress);
    }

    /**
     * Initialize new entity.
     */
    public function init()
    {
        $this->idAttribute = '_id';
        $this->idAttributeType = static::$idTypeAutoIncrement;
        if ($this->skipInit) {
            return;
        }
        $this->initEntityEvents();
        parent::init();
    }

    /**
     * @inheritdoc
     * @return BaseMongoEntityQuery the newly created [[BaseMongoEntityQuery]] or its sub-class instance.
     */
    public static function find()
    {
        $self = static::buildNoInitModel();
        /* @var $self static */
        if (!is_string($self->queryClass)) {
            $self->queryClass = BaseMongoEntityQuery::class;
        }
        $queryClass = $self->queryClass;
        return new $queryClass(get_called_class(), ['noInitModel' => $self]);
    }

    /**
     * @inheritdoc
     * You can override this method if enabled fields cannot meet you requirements.
     * @return array
     */
    public function attributes()
    {
        return $this->enabledFields();
    }
}
