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

use rhosocial\base\models\queries\BaseRedisEntityQuery;
use rhosocial\base\models\traits\EntityTrait;

/**
 * Description of BaseRedisEntityModel
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
abstract class BaseRedisEntityModel extends \yii\redis\ActiveRecord
{
    use EntityTrait;

    /**
     * Initialize new entity.
     */
    public function init()
    {
        if ($this->skipInit) {
            return;
        }
        $this->initEntityEvents();
        parent::init();
    }

    /**
     * @inheritdoc
     * @return \vistart\Models\queries\BaseRedisEntityQuery the newly created [[BaseEntityQuery]] or its sub-class instance.
     */
    public static function find()
    {
        $self = static::buildNoInitModel();
        if (!is_string($self->queryClass)) {
            $self->queryClass = BaseRedisEntityQuery::class;
        }
        $queryClass = $self->queryClass;
        return new $queryClass(get_called_class(), ['noInitModel' => $self]);
    }

    public function attributes()
    {
        return $this->enabledFields();
    }

    public static function primaryKey()
    {
        $model = static::buildNoInitModel();
        if (is_string($model->guidAttribute)) {
            return [$model->guidAttribute];
        }
        return [$model->idAttribute];
    }
}
