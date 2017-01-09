<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\models;

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
                [[$this->guidAttribute], 'unique',],
            ];
        }
        return $rules;
    }

    /**
     * Generate GUID in binary.
     * @return string GUID.
     */
    public static function generateGuid()
    {
        return Number::guid();
    }
    
    public function setGUID($guid)
    {
        $guidAttribute = $this->guidAttribute;
        return (is_string($guidAttribute) && !empty($guidAttribute)) ? $this->$guidAttribute = $guid : null;
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
