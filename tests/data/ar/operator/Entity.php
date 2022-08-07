<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2022 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\tests\data\ar\operator;

use rhosocial\base\models\models\BaseEntityModel;
use rhosocial\base\models\traits\OperatorTrait;

/**
 * Class Entity
 * @package rhosocial\base\models\tests\data\ar\operator
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class Entity extends BaseEntityModel
{
    use OperatorTrait;

    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%operator_entity}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), $this->getOperatorRules());
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), $this->getOperatorBehaviors());
    }
}
