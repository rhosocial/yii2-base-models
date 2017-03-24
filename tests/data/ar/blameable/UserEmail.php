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

namespace rhosocial\base\models\tests\data\ar\blameable;

use Yii;
use rhosocial\base\models\tests\data\ar\User;
use rhosocial\base\models\queries\BaseBlameableQuery;
use rhosocial\base\models\models\BaseBlameableModel;

/**
 * User Email Test Model.
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class UserEmail extends BaseBlameableModel
{
    public $confirmationAttribute = 'confirmed';
    public $confirmCodeAttribute = 'confirm_code';
    public $contentTypeAttribute = 'type';

    const TYPE_HOME = 0;
    const TYPE_WORK = 1;
    const TYPE_OTHER = 0xff;

    public $contentTypes = [
        self::TYPE_HOME => 'home',
        self::TYPE_WORK => 'work',
        self::TYPE_OTHER => 'other',
    ];

    public $updatedByAttribute = false;
    public $contentAttribute = 'email';
    public $contentAttributeRule = ['email', 'message' => 'Please input valid email address.', 'allowName' => true];
    public $enableIP = false;

    public $descriptionAttribute = 'description';

    public function init()
    {
        $this->hostClass = User::class;
        $this->initDescription = Yii::$app->security->generateRandomString();
        parent::init();
    }

    /**
     * @inheritdoc
     */
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

    /**
     * Friendly to IDE.
     * @return BaseBlameableQuery
     */
    public static function find()
    {
        return parent::find();
    }
}
