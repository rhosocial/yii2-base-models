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

namespace rhosocial\base\models\tests\data\ar;

use rhosocial\base\models\models\BaseMongoBlameableModel;

/**
 * @version 1.0
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
     * @return \rhosocial\base\models\queries\BaseMongoBlameableQuery;
     */
    public static function find()
    {
        return parent::find();
    }
}
