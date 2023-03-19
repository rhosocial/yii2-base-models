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

use rhosocial\base\helpers\Number;
use rhosocial\base\models\models\BaseEntityModel;
use rhosocial\base\models\models\BaseMongoEntityModel;
use rhosocial\base\models\queries\BaseMongoEntityQuery;

/**
 * @version 2.0
 * @since 1.0
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

    /**
     *
     * @param array $models
     */
    public static function compositeGUIDs($models): array|string|null
    {
        if (empty($models)) {
            return null;
        }
        if (!is_array($models) && $models instanceof static) {
            return $models->getGUID();
        }
        if (is_string($models) && preg_match(Number::GUID_REGEX, $models)) {
            return $models;
        }
        $guids = [];
        foreach ($models as $model) {
            if ($model instanceof static || $model instanceof BaseEntityModel) {
                $guids[] = $model->getGUID();
            } elseif (is_string($model) && preg_match(Number::GUID_REGEX, $model)) {
                $guids[] = $model;
            } elseif (is_string($model) && strlen($model) == 16) {
                $guids[] = Number::guid(false, false, $model);
            }
        }
        return $guids;
    }
}
