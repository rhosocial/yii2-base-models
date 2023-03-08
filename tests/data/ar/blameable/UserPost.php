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

namespace rhosocial\base\models\tests\data\ar\blameable;

use rhosocial\base\models\tests\data\ar\User;
use rhosocial\base\models\models\BaseBlameableModel;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class UserPost extends BaseBlameableModel
{
    public int $idAttributeLength = 255;

    public bool $idCreatorCombinatedUnique = false;

    public function __construct($config = array()) {
        $this->hostClass = User::class;
        parent::__construct($config);
    }

    public function init()
    {
        $this->hostClass = User::class;
        parent::init();
        $this->setContent(\Yii::$app->security->generateRandomString());
    }

    public string|array $contentAttributeRule = ['string'];

    public static function tableName()
    {
        return '{{%user_post}}';
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
