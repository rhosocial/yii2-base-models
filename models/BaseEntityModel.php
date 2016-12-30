<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link http://vistart.me/
 * @copyright Copyright (c) 2016 vistart
 * @license http://vistart.me/license/
 */

namespace rhosocial\base\models\models;

use rhosocial\base\models\queries\BaseEntityQuery;
use rhosocial\base\models\traits\EntityTrait;
use yii\db\ActiveRecord;
use yii\base\NotSupportedException;

/**
 * The abstract BaseEntityModel is used for entity model class which associates
 * with relational database table.
 * Note: the $idAttribute and $guidAttribute are not be assigned to false
 * simultaneously, and you should set at least one of them as primary key.
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
abstract class BaseEntityModel extends ActiveRecord
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
        $this->checkAttributes();
        parent::init();
    }

    /**
     * Check whether all properties meet the standards. If you want to disable
     * checking, please override this method and return true directly. This
     * method runs when environment is not production or disable debug mode.
     * @return boolean true if all checks pass.
     * @throws NotSupportedException
     */
    public function checkAttributes()
    {
        if (YII_ENV !== YII_ENV_PROD || YII_DEBUG) {
            if (!is_string($this->idAttribute) && empty($this->idAttribute) &&
                !is_string($this->guidAttribute) && empty($this->guidAttribute)) {
                $errorInfo = 'ID and GUID attributes are not be disabled simultaneously in relational database.';
                throw new \yii\base\NotSupportedException($errorInfo);
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     * ------------
     * This static method will take $queryClass property to query class. If it is
     * not a string, The [[BaseEntityQuery]] will be taken.
     * This static method will build non-init model first, so you wouldn't build it any more.
     * @return BaseEntityQuery the newly created [[BaseEntityQuery]]
     * or its extended class instance.
     */
    public static function find()
    {
        $self = static::buildNoInitModel();
        if (!is_string($self->queryClass)) {
            $self->queryClass = BaseEntityQuery::className();
        }
        $queryClass = $self->queryClass;
        return new $queryClass(get_called_class(), ['noInitModel' => $self]);
    }
}
