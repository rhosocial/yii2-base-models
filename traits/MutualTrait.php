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

use rhosocial\base\models\models\BaseUserModel;
use rhosocial\base\models\queries\BaseUserQuery;

/**
 * Description of MutualTrait
 *
 * @property-read mixed $initiator
 * @property mixed $recipient
 * @property-read array $mutualRules
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
trait MutualTrait
{

    public $otherGuidAttribute = 'other_guid';

    /**
     * Get initiator.
     * @return BaseUserQuery
     */
    public function getInitiator()
    {
        return $this->getHost();
    }

    /**
     * Get recipient.
     * @return BaseUserQuery
     */
    public function getRecipient()
    {
        if (!is_string($this->otherGuidAttribute)) {
            return null;
        }
        $hostClass = $this->hostClass;
        $model = $hostClass::buildNoInitModel();
        return $this->hasOne($hostClass::className(), [$model->guidAttribute => $this->otherGuidAttribute]);
    }

    /**
     * Set recipient.
     * @param BaseUserModel $user
     * @return string
     */
    public function setRecipient($user)
    {
        if (!is_string($this->otherGuidAttribute)) {
            return null;
        }
        $otherGuidAttribute = $this->otherGuidAttribute;
        return $this->$otherGuidAttribute = $user->getGUID();
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
                [$this->otherGuidAttribute, 'string', 'max' => 36],
            ];
        }
        return $rules;
    }
}
