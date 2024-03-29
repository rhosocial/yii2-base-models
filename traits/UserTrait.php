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
use yii\base\InvalidArgumentException;
use yii\web\Application;

/**
 * Assemble PasswordTrait, RegistrationTrait and IdentityTrait into UserTrait.
 * This trait can only be used in the class extended from [[BaseEntityModel]],
 * [[BaseMongoEntityModel]], [[BaseRedisEntityModel]], or any other classes used
 * [[EntityTrait]].
 * This trait implements two methods `create()` and `findOneOrCreate()`.
 * Please read the notes of methods and used traits for further detailed usage.
 *
 * @version 2.0
 * @since 1.0
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
     *//*
    public function removeSelf()
    {
        return $this->deregister();
    }*/

    /**
     * Get all rules with current user properties.
     * @return array all rules.
     */
    public function rules(): array
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
     * If it is currently a web application and has logged in, the current login identity will be returned
     * regardless of the parameter passed in.
     * @param ?static $user User instance. The current logged-in user is automatically
     * used if a user is logged in and this parameter will be ignored.
     * @return static|false if current user is valid, it returns as is, otherwise
     * false returned.
     * @throws InvalidArgumentException if the current user is not logged in and
     * the user is not a valid instance.
     */
    public static function isValid(mixed $user): false|static
    {
        if (Yii::$app instanceof Application && !Yii::$app->user->isGuest)
        {
            return Yii::$app->user->identity;
        }
        if (!($user instanceof static)) {
            return false;
        }
        return $user->find()->guid($user)->exists() ? $user : false;
    }
}
