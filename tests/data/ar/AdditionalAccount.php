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

use rhosocial\base\models\models\BaseAdditionalAccountModel;
use Yii;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class AdditionalAccount extends BaseAdditionalAccountModel
{
    public string|false $separateLoginAttribute = 'separate_login';

    public function __construct($config = array())
    {
        $this->hostClass = User::class;
        parent::__construct($config);
    }

    public static function tableName(): string
    {
        return '{{%user_additional_account}}';
    }

    public function attributeLabels(): array
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
