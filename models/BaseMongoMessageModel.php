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

namespace rhosocial\base\models\models;

use MongoDB\BSON\Binary;
use rhosocial\base\models\queries\BaseMongoMessageQuery;
use rhosocial\base\models\queries\BaseUserQuery;
use rhosocial\base\models\traits\MessageTrait;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
abstract class BaseMongoMessageModel extends BaseMongoBlameableModel
{
    use MessageTrait;
    
    public $updatedAtAttribute = false;
    public $updatedByAttribute = false;
    public $expiredAt = 604800; // 7 days.
    
    public function init()
    {
        if (!is_string($this->queryClass) || empty($this->queryClass)) {
            $this->queryClass = BaseMongoMessageQuery::class;
        }
        if ($this->skipInit) {
            return;
        }
        $this->initMessageEvents();
        parent::init();
    }

    /**
     * Get recipient.
     * @return BaseUserQuery
     */
    public function getRecipient()
    {
        if (!is_string($this->otherGuidAttribute) || empty($this->otherGuidAttribute)) {
            throw new \yii\base\InvalidConfigException('Recipient GUID Attribute Not Specified.');
        }
        $hostClass = $this->hostClass;
        $model = $hostClass::buildNoInitModel();
        return $this->hasOne($hostClass::className(), [$model->guidAttribute => 'otherAttribute']);
    }
    
    /**
     * Get updated_by attribute.
     * @return string|null
     */
    public function getOtherAttribute()
    {
        $updatedByAttribute = $this->updatedByAttribute;
        return (!is_string($updatedByAttribute) || empty($updatedByAttribute)) ? null : $this->$updatedByAttribute->getData();
    }

    /**
     * Set recipient.
     * @param BaseUserModel $user
     * @return string
     */
    public function setRecipient($user)
    {
        if (!is_string($this->otherGuidAttribute) || empty($this->otherGuidAttribute)) {
            throw new \yii\base\InvalidConfigException('Recipient GUID Attribute Not Specified.');
        }
        if ($user instanceof BaseUserModel) {
            $user = $user->getGUID();
        }
        $otherGuidAttribute = $this->otherGuidAttribute;
        return $this->$otherGuidAttribute = new Binary($user, Binary::TYPE_UUID);
    }

    /**
     * Get mutual attributes rules.
     * @return array
     */
    public function getMutualRules()
    {
        $rules = [];
        if (is_string($this->otherGuidAttribute)) {
            $rules = [
                [$this->otherGuidAttribute, 'required'],
            ];
        }
        return $rules;
    }
}