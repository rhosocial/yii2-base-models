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

/**
 * @author vistart <i@vistart.me>
 */
class AdditionalAccount extends \rhosocial\base\models\models\BaseAdditionalAccountModel
{
    public static function tableName()
    {
        return '{{%user_additional_account}}';
    }
    
    public function getUser()
    {
        return $this->hasOne(User::class, ['guid' => 'user_guid']);
    }
}