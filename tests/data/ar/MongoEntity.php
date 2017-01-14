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

use rhosocial\base\models\models\BaseMongoEntityModel;
use rhosocial\base\models\queries\BaseMongoEntityQuery;

/**
 * 
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class MongoEntity extends BaseMongoEntityModel
{
    /**
     * @inheritdoc
     */
    public static function collectionName() {
        return ['yii2-base-models', 'entity'];
    }
    
    public static function primaryKey() {
        return [static::buildNoInitModel()->guidAttribute];
    }
    
    /**
     * 
     * @return BaseMongoEntityQuery
     */
    public static function find()
    {
        return parent::find();
    }
}