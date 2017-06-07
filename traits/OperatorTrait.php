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
use Yii;
use yii\base\Event;
use yii\behaviors\BlameableBehavior;

/**
 * OperatorTrait
 *
 * Before using model with this trait, you need to specify a field to store the operator's GUID, and attach the rules
 * and behaviors associated with 'operatorAttribute' to model if you feel it is necessary, like following:
 * ```php
 * public function rules()
 * {
 *     return array_merge(parent::rules(), $this->getOperatorRules());
 * }
 *
 * public function behaviors()
 * {
 *     return array_merge(parent::behaviors(), $this->getOperatorBehaviors());
 * }
 * ```
 * Then, the current logged-in user will be recorded as operator when saving model.
 *
 * @property-read BaseUserModel $operator
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
Trait OperatorTrait
{
    /**
     * @var string|bool the attribute that stores the operator's GUID.
     * If you do not want to use this feature, please set false.
     */
    public $operatorAttribute = 'operator_guid';

    /**
     * Get operator query.
     * If you want to get operator, please access [[$operator]] magic-property.
     * Note: It may return null value! Please check whether the return value is available before accessing.
     * @return BaseUserQuery
     */
    public function getOperator()
    {
        if (empty($this->operatorAttribute) || !is_string($this->operatorAttribute)) {
            return null;
        }
        $userClass = Yii::$app->user->identityClass;
        $noInit = $userClass::buildNoInitModel();
        return $this->hasOne($userClass, [$noInit->guidAttribute => $this->operatorAttribute]);
    }

    /**
     * Set the current logged-in user as operator.
     * Please DO NOT call it directly, unless you know the consequences.
     * @param Event $event
     * @return null|string
     */
    public function onAssignOperator($event)
    {
        $identity = Yii::$app->user->identity;
        if (empty($identity)) {
            return null;
        }
        return $identity->getGUID();
    }

    /**
     * @return array
     */
    public function getOperatorBehaviors()
    {
        if (!empty($this->operatorAttribute) && is_string($this->operatorAttribute)) {
            $behaviors[] = [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => false,
                'updatedByAttribute' => $this->operatorAttribute,
                'value' => [$this, 'onAssignOperator'],
            ];
            return $behaviors;
        }
        return [];
    }

    /**
     * Mark the `operatorAttribute` as safe.
     * @return array
     */
    public function getOperatorRules()
    {
        if (!empty($this->operatorAttribute) && is_string($this->operatorAttribute)) {
            return [
                [$this->operatorAttribute, 'safe'],
            ];
        }
        return [];
    }
}
