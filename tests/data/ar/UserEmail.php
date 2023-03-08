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

namespace rhosocial\base\models\tests\data\ar;

use rhosocial\base\models\models\BaseBlameableModel;
use Yii;

/**
 * User Email Test Model.
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class UserEmail extends BaseBlameableModel
{
    public string|false $confirmationAttribute = 'confirmed';
    public string|false $confirmCodeAttribute = 'confirm_code';
    public string|false $contentTypeAttribute = 'type';

    const TYPE_HOME = 0;
    const TYPE_WORK = 1;
    const TYPE_OTHER = 0xff;
    
    public array|false $contentTypes = [
        self::TYPE_HOME => 'home',
        self::TYPE_WORK => 'work',
        self::TYPE_OTHER => 'other',
    ];

    public string|false $updatedByAttribute = false;
    public string|array|false $contentAttribute = 'email';
    public array $contentAttributeRule = ['email', 'message' => 'Please input valid email address.', 'allowName' => true];
    public int $enableIP = 0;

    public function init()
    {
        $this->userClass = User::class;
        parent::init();
    }

    public static function tableName()
    {
        return '{{%user_email}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'guid' => Yii::t('app', 'Guid'),
            'user_guid' => Yii::t('app', 'User Guid'),
            'id' => Yii::t('app', 'ID'),
            'email' => Yii::t('app', 'Email'),
            'type' => Yii::t('app', 'Type'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'confirmed' => Yii::t('app', 'Confirmed'),
            'confirmed_at' => Yii::t('app', 'Confirmed At'),
            'confirm_code' => Yii::t('app', 'Confirm Code'),
        ];
    }
}
