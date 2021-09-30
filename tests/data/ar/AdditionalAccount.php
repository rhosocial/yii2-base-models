<?php

/**
 *   _   __ __ _____ _____ ___  ____  _____
 *  | | / // // ___//_  _//   ||  __||_   _|
 *  | |/ // /(__  )  / / / /| || |     | |
 *  |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\tests\data\ar;

use Yii;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class AdditionalAccount extends \rhosocial\base\models\models\BaseAdditionalAccountModel
{
    public $seperateLoginAttribute = 'seperate_login';

    public function __construct($config = array())
    {
        $this->hostClass = User::class;
        parent::__construct($config);
    }

    public static function tableName()
    {
        return '{{%user_additional_account}}';
    }

    /**
     *
     * @return \rhosocial\base\models\queries\BaseUserQuery
     */
    /*
    public function getUser()
    {
        return $this->hasOne(User::class, ['guid' => 'user_guid']);
    }
    */
    public function attributeLabels()
    {
        return [
            'guid' => Yii::t('app', 'Guid'),
            'user_guid' => Yii::t('app', 'User Guid'),
            'id' => Yii::t('app', 'ID'),
            'pass_hash' => Yii::t('app', 'Pass Hash'),
            'enable_login' => Yii::t('app', 'Enable Login'),
            'content' => Yii::t('app', 'Content'),
            'source' => Yii::t('app', 'Source'),
            'description' => Yii::t('app', 'Description'),
            'ip' => Yii::t('app', 'Ip'),
            'ip_type' => Yii::t('app', 'Ip Type'),
            'confirmed' => Yii::t('app', 'Confirmed'),
            'confirmed_at' => Yii::t('app', 'Confirmed At'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
