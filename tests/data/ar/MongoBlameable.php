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

namespace rhosocial\base\models\tests\data\ar;

use rhosocial\base\models\models\BaseMongoBlameableModel;
use rhosocial\base\models\queries\BaseMongoEntityQuery;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class MongoBlameable extends BaseMongoBlameableModel
{
    public static function collectionName() {
        return ['yii2-base-models', 'blameable'];
    }

    public function init() {
        $this->hostClass = User::class;
        parent::init();
    }

    /**
     * 
     * @return BaseMongoEntityQuery;
     */
    public static function find()
    {
        return parent::find();
    }
}
