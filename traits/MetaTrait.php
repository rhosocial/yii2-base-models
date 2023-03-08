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

namespace rhosocial\base\models\traits;

use Yii;

/**
 * Description of MetaTrait
 *
 * @property string $key
 * @property string $value
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
trait MetaTrait
{

    /**
     * Store the guid of blame.
     * @return array
     */
    public function behaviors()
    {
        return array_merge($this->getMetaBehaviors(), parent::behaviors());
    }

    public function getKey()
    {
        return $this->id;
    }

    public function setKey($key)
    {
        return $this->id = $key;
    }

    public function getValue()
    {
        return $this->content;
    }

    public function setValue($value)
    {
        return $this->content = $value;
    }

    /**
     * Skip all behaviors of parent class.
     * @return array
     */
    public function getMetaBehaviors()
    {
        return [];
    }

    /**
     * Get meta value by specified key. If key doesn't exist, null will be given.
     * @param string $key meta key.
     * @return string|null meta value.
     */
    public static function get($key): ?string
    {
        $noInitModel = static::buildNoInitModel();
        $model = static::find()->where([$noInitModel->idAttribute => $key])->one();
        if ($model) {
            return $model->value;
        }
        return null;
    }

    /**
     * Get meta values by specified keys. If one of keys doesn't exists, it will
     * not appear in return array.
     * @param string[] $keys
     * @return array meta key-value pairs.
     */
    public static function gets($keys = null)
    {
        $noInitModel = static::buildNoInitModel();
        $query = static::find();
        if ($keys == null) {
            $models = $query->all();
        } elseif (is_array($keys)) {
            $array = [];
            foreach ($keys as $key) {
                if (is_string($key) && strlen($key)) {
                    $array[] = $key;
                }
            }
            $models = $query->where([$noInitModel->idAttribute => $array])->all();
        }
        $result = [];
        foreach ($models as $key => $model) {
            $result[$model->key] = $model->value;
        }
        return $result;
    }

    /**
     * Set value.
     * @param string $key
     * @param string|null $value
     * @param string|null $createdBy
     * @return bool
     */
    public static function set(string $key, ?string $value = null, ?string $createdBy = null): bool
    {
        $noInitModel = static::buildNoInitModel();
        $model = static::find()->where([$noInitModel->idAttribute => $key])->one();
        if ($value == null && $model) {
            return $model->delete();
        }
        if (!$model) {
            if (empty($createdBy) && !Yii::$app->user->isGuest) {
                $createdBy = Yii::$app->user->identity->getGUID();
            }
            $model = new static([$noInitModel->idAttribute => $key, $noInitModel->createdByAttribute => $createdBy]);
        }
        $model->value = $value;
        return $model->save();
    }

    /**
     * Set values in batch.
     * @param array|null $keys meta key-value pairs.
     * @param string|null $createdBy
     * @return bool if $keys is not an array.
     */
    public static function sets(?array $keys, ?string $createdBy = null): bool
    {
        if (!is_array($keys)) {
            return false;
        }
        foreach ($keys as $key => $value) {
            static::set($key, $value, $createdBy);
        }
        return true;
    }

    public static function remove($key)
    {
        return static::set($key);
    }
}
