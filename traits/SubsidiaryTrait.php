<?php

/**
 *   _   __ __ _____ _____ ___  ____  _____
 *  | | / // // ___//_  _//   ||  __||_   _|
 *  | |/ // /(__  )  / / / /| || |     | |
 *  |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2017 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\traits;

use yii\base\InvalidConfigException;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
trait SubsidiaryTrait
{
    /**
     * @var string[] Subsidiary map.
     * Array key represents class alias,
     * array value represents the full qualified class name corresponds to the alias.
     *
     * For example:
     * ```php
     * public $subsidiaryMap = [
     *     'Profile' => 'app\models\user\Profile',
     * ];
     * ```
     * or:
     * ```php
     * public $subsidiaryMap = [
     *     'Profile' => [
     *         'class' => 'app\models\user\Profile',
     *         'max' => 1,
     *     ]
     * ];
     *
     * If you want to create subsidiary model and the class is not found, the array elements will be taken.
     */
    public $subsidiaryMap = [];
    
    /**
     * Add subsidiary.
     * @param string $name
     * @param string|array $config
     * @return boolean
     * @throws InvalidConfigException
     */
    public function addSubsidiary($name, $config)
    {
        if (!is_string($name) || empty($name)) {
            throw new InvalidConfigException('Subsidiary name not specified.');
        }
        $name = strtolower($name);
        if (!is_array($config)) {
            if (is_string($config) && !empty($config)) {
                $this->subsidiaryMap[$name] = ['class' => $config];
            } else {
                throw new InvalidConfigException('Subsidiary class not specified.');
            }
        } else {
            if (isset($config['class']) && class_exists($config['class'])) {
                $this->subsidiaryMap[$name] = $config;
            } else {
                throw new InvalidConfigException('Subsidiary class not specified.');
            }
        }
        return true;
    }
    
    /**
     * Remove subsidiary.
     * @param string $name
     * @return boolean
     */
    public function removeSubsidiary($name)
    {
        $name = strtolower($name);
        if (array_key_exists($name, $this->subsidiaryMap)) {
            unset($this->subsidiaryMap[$name]);
            return true;
        }
        return false;
    }
    
    /**
     * Get subsidiary class.
     * @param string $name
     * @return string
     */
    public function getSubsidiaryClass($name)
    {
        $name = strtolower($name);
        if (array_key_exists($name, $this->subsidiaryMap) && array_key_exists('class', $this->subsidiaryMap[$name])) {
            return class_exists($this->subsidiaryMap[$name]['class']) ? $this->subsidiaryMap[$name]['class'] : null;
        }
        return null;
    }
    
    public function getSubsidiaries($name, $limit = 'all', $page = 0)
    {
        $class = $this->getSubsidiaryClass($name);
        if (empty($class)) {
            return null;
        }
        $query = $class::find();
        if (!method_exists($query, 'createdBy')) {
            return null;
        }
        return $class::find()->createdBy($this)->page($limit, $page)->all();
    }
    
    public function __call($name, $arguments)
    {
        if (strpos(strtolower($name), "create") === 0) {
            $class = strtolower(substr($name, 6));
            $config = (isset($arguments) && isset($arguments[0])) ? $arguments[0] : [];
            return $this->createSubsidiary($class, $config);
        }
        return parent::__call($name, $arguments);
    }

    /**
     * Find existed, or create new model.
     * If model to be found doesn't exist, and $config is null, the parameter
     * `$condition` will be regarded as properties of new model.
     * If you want to know whether the returned model is new model, please check
     * the return value of `getIsNewRecord()` method.
     * @param string $className Full qualified class name.
     * @param array $condition Search condition, or properties if not found and
     * $config is null.
     * @param array $config new model's configuration array. If you specify this
     * parameter, the $condition will be skipped when created one.
     * @return [[$className]] the existed model, or new model created by specified
     * condition or configuration.
     */
    public function findOneOrCreate($className, $condition = [], $config = null)
    {
        $entity = new $className(['skipInit' => true]);
        if (!isset($condition[$entity->createdByAttribute])) {
            $condition[$entity->createdByAttribute] = $this->getGUID();
        }
        $model = $className::findOne($condition);
        if (!$model) {
            if ($config === null || !is_array($config)) {
                $config = $condition;
            }
            $model = $this->create($className, $config);
        }
        return $model;
    }

    /**
     * Create new entity model associated with current user. The model to be created
     * must be extended from [[BaseBlameableModel]], [[BaseMongoBlameableModel]],
     * [[BaseRedisBlameableModel]], or any other classes used [[BlameableTrait]].
     * if $config does not specify `userClass` property, self will be assigned to.
     * @param string $className Full qualified class name.
     * @param array $config name-value pairs that will be used to initialize
     * the object properties.
     * @param boolean $loadDefault Determines whether loading default values
     * after entity model created.
     * Notice! The [[\yii\mongodb\ActiveRecord]] and [[\yii\redis\ActiveRecord]]
     * does not support loading default value. If you want to assign properties
     * with default values, please define the `default` rule(s) for properties in
     * `rules()` method and return them by yourself if you don't specified them in config param.
     * @param boolean $skipIfSet whether existing value should be preserved.
     * This will only set defaults for attributes that are `null`.
     * @return [[$className]] new model created with specified configuration.
     */
    public function create($className, $config = [], $loadDefault = true, $skipIfSet = true)
    {
        if (!isset($config['hostClass'])) {
            $config['hostClass'] = static::class;
        }
        if (isset($config['class'])) {
            unset($config['class']);
        }
        $entity = new $className($config);
        $entity->setHost($this);
        if ($loadDefault && method_exists($entity, 'loadDefaultValues')) {
            $entity->loadDefaultValues($skipIfSet);
        }
        return $entity;
    }

    /**
     *
     * @param string $name
     * @param array $config
     * @return type
     */
    public function createSubsidiary($name, $config)
    {
        if (!is_string($name) || empty($name)) {
            return null;
        }
        $className = '';
        if (class_exists($name)) {
            $className = $name;
        } elseif (array_key_exists($name, $this->subsidiaryMap)) {
            $className = $this->getSubsidiaryClass($name);
        } else {
            return null;
        }
        return $this->create($className, $config);
    }
}
