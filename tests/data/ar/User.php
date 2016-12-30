<?php
/*
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 vistart
 * @license https://vistart.me/license/
 */
namespace vistart\Models\tests\data\ar;
use Yii;
/**
 * Description of ActiveRecord
 * @property-read UserEmail[] $userEmails
 * @property-read AdditionalAccount[] $additionalAccounts
 * @author vistart <i@vistart.me>
 * @since 1.0
 */
class User extends \rhosocial\base\models\models\BaseUserModel {
    public $idAttributePrefix = '4';
    public $idAttributeType = 1;
    public $idAttributeLength = 8;
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%user}}';
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'user_uuid' => Yii::t('app', 'User Uuid'),
            'user_id' => Yii::t('app', 'User ID'),
            'pass_hash' => Yii::t('app', 'Pass Hash'),
            'create_time' => Yii::t('app', 'Create Time'),
            'update_time' => Yii::t('app', 'Update Time'),
            'ip_1' => Yii::t('app', 'Ip 1'),
            'ip_2' => Yii::t('app', 'Ip 2'),
            'ip_3' => Yii::t('app', 'Ip 3'),
            'ip_4' => Yii::t('app', 'Ip 4'),
            'ip_type' => Yii::t('app', 'Ip Type'),
            'auth_key' => Yii::t('app', 'Auth Key'),
            'access_token' => Yii::t('app', 'Access Token'),
            'status' => Yii::t('app', 'Status'),
            'source' => Yii::t('app', 'Source'),
        ];
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserEmails() {
        return $this->hasMany(UserEmail::className(), ['user_guid' => 'guid'])->inverseOf('user');
    }
    
    public function getAdditionalAccounts() {
        return $this->hasMany(AdditionalAccount::className(), ['user_guid' => 'guid'])->inverseOf('user');
    }
    /**
     * Friendly to IDE.
     * @return \rhosocial\base\models\queries\BaseUserQuery
     */
    public static function find()
    {
        return parent::find();
    }
}