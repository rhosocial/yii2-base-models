<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2017 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\traits;

use Yii;
use yii\base\InvalidParamException;

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
     *//*
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
     * Check whether the user is valid.
     * @param static $user User instance. The current logged-in user is automatically
     * used if a user is logged in and this parameter is null.
     * @return static|false if current user is valid, it return as is, otherwise
     * false returned.
     * @throws InvalidParamException if the current user is not logged in and
     * the user is not a valid instance.
     */
    public static function isValid($user)
    {
        if (Yii::$app->user->isGuest && (empty($user) || !($user instanceof static))) {
            throw new InvalidParamException('User Not Specified.');
        }
        if ((empty($user) || !($user instanceof static)) && !Yii::$app->user->isGuest) {
            $user = Yii::$app->user->identity;
        }
        return $user->find()->guid($user)->exists() ? $user : false;
    }
}
