<?php

namespace rhosocial\base\models\tests\data\ar;

use Yii;

/**
 * This is the model class for table "{{%user_comment}}".
 *
 * @property string $guid
 * @property string $id
 * @property string $parent_guid
 * @property string $user_guid
 * @property string $content
 * @property string $created_at
 * @property string $updated_at
 * @property string $ip
 * @property integer $ip_type
 * @property integer $confirmed
 * @property string $confirmed_at
 * @property string $confirm_code
 *
 * @property User $userGu
 */
class UserComment extends \rhosocial\base\models\models\BaseBlameableModel
{
    public $parentAttribute = 'parent_guid';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_comment}}';
    }

    public function init()
    {
        $this->userClass = User::class;
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'guid' => Yii::t('app', 'Guid'),
            'id' => Yii::t('app', 'ID'),
            'parent_guid' => Yii::t('app', 'Parent Guid'),
            'user_guid' => Yii::t('app', 'User Guid'),
            'content' => Yii::t('app', 'Content'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'ip' => Yii::t('app', 'Ip'),
            'ip_type' => Yii::t('app', 'Ip Type'),
            'confirmed' => Yii::t('app', 'Confirmed'),
            'confirmed_at' => Yii::t('app', 'Confirmed At'),
            'confirm_code' => Yii::t('app', 'Confirm Code'),
        ];
    }

    /**
     * Friendly to IDE.
     * @return \rhosocial\base\models\queries\BaseBlameableQuery
     */
    public static function find()
    {
        return parent::find();
    }
}
