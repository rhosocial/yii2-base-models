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

namespace rhosocial\base\models\tests\data\ar;

use Yii;
use rhosocial\base\models\queries\BaseBlameableQuery;
use rhosocial\base\models\queries\BaseUserQuery;
use rhosocial\base\models\tests\data\ar\blameable\UserEmail;

/**
 * Description of User
 * @property-read UserEmail[] $emails
 * @property-read AdditionalAccount[] $additionalAccounts
 * @author vistart <i@vistart.me>
 * @version 2.0
 * @since 1.0
 */
class User extends \rhosocial\base\models\models\BaseUserModel
{
    public string $idAttributePrefix = '4';
    public int $idAttributeType = 1;
    public int $idAttributeLength = 8;

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'guid' => Yii::t('app', 'Guid'),
            'id' => Yii::t('app', 'ID'),
            'pass_hash' => Yii::t('app', 'Pass Hash'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'ip' => Yii::t('app', 'Ip'),
            'ip_type' => Yii::t('app', 'Ip Type'),
            'auth_key' => Yii::t('app', 'Auth Key'),
            'access_token' => Yii::t('app', 'Access Token'),
            'password_reset_token' => Yii::t('app', 'Password Reset Token'),
            'status' => Yii::t('app', 'Status'),
            'source' => Yii::t('app', 'Source'),
        ];
    }

    /**
     * @return BaseBlameableQuery
     */
    public function getEmails(): BaseBlameableQuery
    {
        return $this->hasMany(UserEmail::class, ['user_guid' => 'guid'])->inverseOf('user');
    }

    /**
     * @return BaseBlameableQuery
     */
    public function getAdditionalAccounts(): BaseBlameableQuery
    {
        return $this->hasMany(AdditionalAccount::class, ['user_guid' => 'guid'])->inverseOf('user');
    }

    /**
     * Friendly to IDE.
     * @return BaseUserQuery
     */
    public static function find(): BaseUserQuery
    {
        return parent::find();
    }
}
