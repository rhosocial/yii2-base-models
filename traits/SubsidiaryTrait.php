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

namespace rhosocial\base\models\traits;

use yii\base\InvalidConfigException;

/**
 * SubsidiaryTrait.
 * The Trait is used to help the model manage its subsidiary models.
 *
 * For example:
 * ```php
 * $user->addSubsidiaryClass("email", ["class" => Email::class]);
 * $email = $user->createEmail(['content' => 'i@vistart.me']);
 * $email->save();
 * ```
 * @version 2.0
 * @since 1.0
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
```php
public $subsidiaryMap = [
    'Profile' => [
        'class' => 'app\models\user\Profile',
    ],
];
```
     *
     * The other elements will be taken if subsidiary configuration does not specify.
     * If you want to create subsidiary model and the class is not found, the array elements will be taken.
     */
    public array $subsidiaryMap = [];

    /**
     * Add subsidiary class to map.
     * @param ?string $name Subsidiary name, case-insensitive.
     * @param array|string|null $config If this parameter is string, it will be regarded as class name.
     * If this parameter is array, you should specify `class`, and the class should be existed.
     * @return boolean True if the class added.
     * @throws InvalidConfigException throws if subsidiary name is not specified or class is not
     * specified.
     */
    public function addSubsidiaryClass(?string $name, array|string|null $config): bool
    {
        if (empty($name)) {
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
     * @param string $name Subsidiary name, case-insensitive.
     * @return boolean
     */
    public function removeSubsidiary(string $name): bool
    {
        $name = strtolower($name);
        if (array_key_exists($name, $this->subsidiaryMap)) {
            unset($this->subsidiaryMap[$name]);
            return true;
        }
        return false;
    }

    /**
     * Get subsidiary class according name.
     * @param string $name Subsidiary name, case-insensitive.
     * @return string|null
     */
    public function getSubsidiaryClass(string $name): ?string
    {
        $name = strtolower($name);
        if (array_key_exists($name, $this->subsidiaryMap) && array_key_exists('class', (array)$this->subsidiaryMap[$name])) {
            return class_exists($this->subsidiaryMap[$name]['class']) ? $this->subsidiaryMap[$name]['class'] : null;
        }
        return null;
    }

    /**
     * Check whether the user has a subsidiary model.
     * @param string $name Subsidiary name, case insensitive.
     * @return bool
     */
    public function hasSubsidiary(string $name): bool
    {
        $class = $this->getSubsidiaryClass($name);
        if (empty($class)) {
            return false;
        }
        $query = $class::find();
        if (!method_exists($query, 'createdBy')) {
            return false;
        }
        return $query->createdBy($this)->exists();
    }

    /**
     * Get subsidiaries.
     * @param string $name Subsidiary name, case-insensitive.
     * @param string $limit
     * @param int $page
     * @return ?array
     */
    public function getSubsidiaries(string $name, string $limit = 'all', int $page = 0): ?array
    {
        $class = $this->getSubsidiaryClass($name);
        if (empty($class)) {
            return null;
        }
        $query = $class::find();
        if (!method_exists($query, 'createdBy')) {
            return null;
        }
        return $query->createdBy($this)->page($limit, $page)->all();
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (str_starts_with(strtolower($name), "create")) {
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
            if (!is_array($config)) {
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
     * if $config does not specify `hostClass` property, self will be assigned to.
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
     * Create subsidiary model.
     * @param string $name Subsidiary name, case-insensitive.
     * @param array $config Subsidiary model configuration array.
     * @return mixed
     */
    public function createSubsidiary(string $name, array $config): mixed
    {
        if (empty($name)) {
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
