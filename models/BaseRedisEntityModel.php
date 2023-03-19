<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2023 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\models;

use rhosocial\base\helpers\Number;
use rhosocial\base\models\queries\BaseRedisEntityQuery;
use rhosocial\base\models\traits\EntityTrait;
use yii\redis\ActiveRecord;

/**
 * Description of BaseRedisEntityModel
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
abstract class BaseRedisEntityModel extends ActiveRecord
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
     * @return BaseRedisEntityQuery the newly created [[BaseEntityQuery]] or its sub-class instance.
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

    /**
     * Returns the list of all attribute names of the model.
     * You can override this method if enabled fields cannot meet your requirements.
     * @return array
     */
    public function attributes()
    {
        return $this->enabledFields();
    }

    /**
     * Either [[guidAttribute]] or [[idAttribute]] should be enabled.
     * You can override this method if GUID or ID attribute cannot meet your
     * requirements.
     * @return array
     */
    public static function primaryKey()
    {
        $model = static::buildNoInitModel();
        if (is_string($model->guidAttribute) && !empty($model->guidAttribute)) {
            return [$model->guidAttribute];
        }
        return [$model->idAttribute];
    }
}
