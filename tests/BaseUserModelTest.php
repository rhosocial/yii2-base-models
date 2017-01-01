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
use rhosocial\base\helpers\Number;
use rhosocial\base\helpers\IP;
use Yii;

class BaseUserModelTest extends TestCase
{
    public function testInit()
    {
        $users = [];
        for ($i = 0; $i < 100; $i++) {
            $users[] = new User();
        }
        foreach ($users as $key => $user) {
            if ($user->register() !== true) {
                unset($users[$key]);
            } else {
                //echo $user->readableGUID . ' | ' . $user->id . "\n";
            }
        }
        
        $count = count($users);
        $this->assertEquals(100, $count, "100 users should be registered.");
        foreach ($users as $user) {
            if ($user->deregister() !== true) {
                $this->assertTrue(false);
            }
        }
    }
    
    public function testGUID()
    {
        $user = new User();
        $guid = $user->GUID;
        $readableGUID = Number::guid(false, false, $guid);
        
        // 可阅读 GUID 值应当与 $user 的 readableGUID 值一致。
        $this->assertEquals($readableGUID, $user->readableGUID);
        
        // 注册必须成功。
        $this->assertTrue($user->register());
        
        // 从数据库中读出用户。
        $existedUser = User::findOne($guid);
        
        // 用户必须存在。
        $this->assertNotNull($existedUser);
        
        // $guid 值必须与数据库中的内容保持一致。
        $this->assertEquals($guid, $existedUser->guid);
        
        // 可阅读 GUID 值应与数据库中取出的二进制值转换而成的可阅读 GUID 值保持一致。
        $this->assertEquals($readableGUID, $existedUser->readableGUID);
        
        // 注销必须成功。
        $this->assertTrue($user->deregister());
    }
}