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

namespace rhosocial\base\models\models;

use rhosocial\base\models\queries\BaseUserQuery;
use rhosocial\base\models\traits\UserTrait;
use yii\web\IdentityInterface;

/**
 * The abstract BaseUserModel is used for user identity class.
 * For example, you should create a table for user model before you want to
 * define a User class used for representing a user. Then, you can use base
 * user model generator to generate a new user model, like following:
 * ~~~php
 * * @property string $guid
 * class User extends \rhosocial\base\models\models\BaseUserModel {
 *     public static function tableName() {
 *         return <table_name>;
 *     }
 *     public static function attributeLabels() {
 *         return [
 *             <All labels.>
 *         ];
 *     }
 * }
 * ~~~
 *
 * Well, if you want to register a new user, you should create a new user
 * instance, and prepare attributes for it. then call the `register()` method.
 * like following:
 * ~~~php
 * $user = new User(['password' => '123456']);
 * $user->register();
 * ~~~
 * If there is not only one user instance to be stored in database, but also
 * other associated models, such as Profile class, should be stored
 * synchronously, you can prepare their models and give them to parameter of
 * `register()` method, like following:
 * ~~~php
 * $profile = new Profile();
 * $user->register([$profile]);
 * ~~~
 * Note: you should supplement `get<ModelName>()` method(s) by yourself, or by
 * generator.
 * @see \rhosocial\base\models\models\BaseEntityModel
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
abstract class BaseUserModel extends BaseEntityModel implements IdentityInterface
{
    use UserTrait;

    /**
     * Initialize user model.
     * This procedure will append events used for initialization of `status` and
     * `source` attributes.
     * When `$skipInit` is assigned to `false`, the above processes will be skipped.
     * If you want to modify or override this method, you should add `parent::init()`
     * statement at the end of your init() method.
     */
    public function init()
    {
        if (!is_string($this->queryClass)) {
            $this->queryClass = BaseUserQuery::class;
        }
        if ($this->skipInit) {
            return;
        }
        $this->on(self::$eventNewRecordCreated, [$this, 'onInitStatusAttribute']);
        $this->on(self::$eventNewRecordCreated, [$this, 'onInitSourceAttribute']);
        $this->on(self::$eventNewRecordCreated, [$this, 'onInitAuthKey']);
        $this->on(self::$eventNewRecordCreated, [$this, 'onInitAccessToken']);
        $this->on(self::$eventNewRecordCreated, [$this, 'onInitPasswordResetToken']);
        $this->on(self::$eventAfterSetPassword, [$this, 'onAfterSetNewPassword']);
        parent::init();
    }
}
