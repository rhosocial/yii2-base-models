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

use rhosocial\base\models\models\BaseMongoMessageModel;
use rhosocial\base\models\queries\BaseMongoBlameableQuery;

/**
 * @author vistart <i@vistart.me>
 */
class MongoMessage extends BaseMongoMessageModel
{
    public function init()
    {
        $this->expiredRemovingCallback = [$this, 'removeExpired'];
        parent::init();
    }
    
    public static function removeExpired($model)
    {
        return $model->delete();
    }
    
    public static function collectionName()
    {
        return ['yii2-base-models', 'message'];
    }
    
    /**
     * Friendly to IDE.
     * @return BaseMongoBlameableQuery
     */
    public static function find()
    {
        return parent::find();
    }
}