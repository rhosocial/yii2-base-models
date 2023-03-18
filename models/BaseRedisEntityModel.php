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

    public function getGUIDRules(): array
    {
        $rules = [];
        if (!empty($this->guidAttribute)) {
            $rules = [
                [[$this->guidAttribute], 'required',],
                [[$this->guidAttribute], 'unique',],
                [[$this->guidAttribute], 'string', 'max' => 36],
            ];
        }
        return $rules;
    }

    public function getGUID(): ?string
    {
        $guidAttribute = $this->guidAttribute;
        return (!is_string($guidAttribute) || empty($guidAttribute)) ? null : $this->$guidAttribute;
    }

    public function setGUID($guid)
    {
        $guidAttribute = $this->guidAttribute;
        if (!is_string($guidAttribute) || empty($guidAttribute)) {
            return null;
        }
        if (is_string($guid) && strlen($guid) == 16) {
            $guid = Number::guid(false, true, $guid);
        }
        return $this->$guidAttribute = $guid;
    }

    /**
     * Check if the $guid existed in current database table.
     * @param string $guid the GUID to be checked.
     * @return boolean Whether the $guid exists or not.
     */
    public static function checkGuidExists(string $guid): bool
    {
        if (strlen($guid) == 16) {
            $binary = Number::guid(false, true, $guid);
        } elseif (preg_match(Number::GUID_REGEX, $guid)) {
            $binary = $guid;
        } else {
            return false;
        }
        return static::findOne($binary) !== null;
    }

    /**
     *
     * @param array $models
     */
    public static function compositeGUIDs($models) {
        if (empty($models)) {
            return null;
        }
        if (!is_array($models) && $models instanceof static) {
            return Number::guid(false, true, $models->getGUID());
        }
        if (is_string($models) && strlen($models) == 16) {
            return Number::guid(false, true, $models);
        }
        $guids = [];
        foreach ($models as $model) {
            if ($model instanceof static || $model instanceof BaseEntityModel) {
                $guids[] = Number::guid(false, true, $model->getGUID());
            } elseif (is_string($model) && preg_match(Number::GUID_REGEX, $model)) {
                $guids[] = $model;
            } elseif (is_string($model) && strlen($model) == 16) {
                $guids[] = Number::guid(false, true, $model);
            }
        }
        return $guids;
    }
}
