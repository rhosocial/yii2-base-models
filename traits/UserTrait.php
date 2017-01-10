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

namespace rhosocial\base\models\traits;

/**
 * Assemble PasswordTrait, RegistrationTrait and IdentityTrait into UserTrait.
 * This trait can only be used in the class extended from [[BaseEntityModel]],
 * [[BaseMongoEntityModel]], [[BaseRedisEntityModel]], or any other classes used
 * [[EntityTrait]].
 * This trait implements two methods `create()` and `findOneOrCreate()`.
 * Please read the notes of methods and used traits for further detailed usage.
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
trait UserTrait
{
    use PasswordTrait,
        RegistrationTrait,
        IdentityTrait;

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
        if (!isset($config['userClass'])) {
            $config['userClass'] = static::class;
        }
        if (isset($config['class'])) {
            unset($config['class']);
        }
        $entity = new $className($config);
        $entity->setUser($this);
        if ($loadDefault && method_exists($entity, 'loadDefaultValues')) {
            $entity->loadDefaultValues($skipIfSet);
        }
        return $entity;
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
            $condition[$entity->createdByAttribute] = $this->guid;
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
     * This method is only used for overriding [[removeSelf()]] in [[TimestampTrait]].
     * @see deregister()
     * @return boolean
     */
    public function removeSelf()
    {
        return $this->deregister();
    }

    /**
     * Get all rules with current user properties.
     * @return array all rules.
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            $this->getPasswordHashRules(),
            $this->getPasswordResetTokenRules(),
            $this->getSourceRules(),
            $this->getStatusRules(),
            $this->getAuthKeyRules(),
            $this->getAccessTokenRules()
        );
    }
    
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
     * @see normalizeSubsidiaryClass
     */
    public $subsidiaryMap = [];
    
    /**
     * 
     * @param string $class
     * @return integer|null
     */
    public function getSubsidiaryInstanceMax($class)
    {
        if (array_key_exists($class, $this->subsidiaryMap) && class_exists($this->subsidiaryMap[$class]['class'])) {
            return array_key_exists('max', $this->subsidiaryMap[$class]) ? $this->subsidiaryMap[$class]['max'] : null;
        }
        return null;
    }
    
    /**
     * 
     * @param type $class
     * @param type $config
     * @return type
     * @todo 区分字符串和类的实例两种情况。
     */
    public function createSubsidiary($class, $config = [])
    {
        if (!is_string($class) || empty($class)) {
            return null;
        }
        $className = '';
        if (class_exists($class)) {
            $className = $class;
        } else
        if (array_key_exists($class, $this->subsidiaryMap)) {
            if (class_exists($this->subsidiaryMap[$class])) {
                $className = $this->subsidiaryMap[$class];
            } else
            if (class_exists($this->subsidiaryMap[$class]['class'])) {
                $className = $this->subsidiaryMap[$class]['class'];
            }
        } else {
            return null;
        }
        return $this->create($className, $config);
    }
    
    /**
     * 
     * @param string $name
     * @param array $params
     * @return type
     */
    public function __call($name, $params)
    {
        if (strpos(strtolower($name), "create") === 0) {
            $class = substr($name, 6);
            $config = (isset($params) && isset($params[0])) ? $params[0] : [];
            return $this->createSubsidiary($class, $config);
        }
        return parent::__call($name, $params);
    }
}
