<?php

/**
 *   _   __ __ _____ _____ ___  ____  _____
 *  | | / // // ___//_  _//   ||  __||_   _|
 *  | |/ // /(__  )  / / / /| || |     | |
 *  |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2017 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\tests\data\ar\blameable;

use rhosocial\base\models\tests\data\ar\User;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class Meta extends \rhosocial\base\models\models\BaseMetaModel
{
    public function __construct($config = array())
    {
        $this->hostClass = User::class;
        parent::__construct($config);
    }

    public static function tableName() {
        return '{{%user_meta}}';
    }
}
