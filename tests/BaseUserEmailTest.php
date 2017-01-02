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

namespace rhosocial\base\models\tests;

use rhosocial\base\models\tests\data\ar\User;
use rhosocial\base\models\tests\data\ar\UserEmail;
use Yii;

/**
 * @author vistart <i@vistart.me>
 */
class BaseUserEmailTest extends TestCase
{
    public function testInit()
    {
        
    }
    
    public function testNew()
    {
        $email = new UserEmail();
        
        $this->assertNotNull($email);
    }
}