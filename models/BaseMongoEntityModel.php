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

use rhosocial\base\models\queries\BaseMongoEntityQuery;
use rhosocial\base\models\traits\EntityTrait;
use yii\mongodb\ActiveRecord;

/**
 * Description of BaseMongoEntityModel
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
abstract class BaseMongoEntityModel extends ActiveRecord
{
    use EntityTrait;

    /**
     * Initialize new entity.
     */
    public function init()
    {
        $this->idAttribute = '_id';
        $this->idAttributeType = static::$idTypeAutoIncrement;
        if ($this->skipInit) {
            return;
        }
        $this->initEntityEvents();
        parent::init();
    }

    /**
     * @inheritdoc
     * @return BaseMongoEntityQuery the newly created [[BaseEntityQuery]] or its sub-class instance.
     */
    public static function find()
    {
        $self = static::buildNoInitModel();
        if (!is_string($self->queryClass)) {
            $self->queryClass = BaseMongoEntityQuery::className();
        }
        $queryClass = $self->queryClass;
        return new $queryClass(get_called_class(), ['noInitModel' => $self]);
    }

    public function attributes()
    {
        return $this->enabledFields();
    }
}
