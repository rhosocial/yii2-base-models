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
    /**
     * @group user
     */
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
    
    /**
     * @group user
     * @depends testInit
     */
    public function testNewUser()
    {
        $user = new User();
        $this->assertNotNull($user);
        
        $this->assertTrue($user->register());
        
        // 状态码应该为 1，不能为 0。
        $statusAttribute = $user->statusAttribute;
        $this->assertEquals(1, $user->$statusAttribute);
        $this->assertTrue($user->deregister());
    }
    
    /**
     * @group user
     * @depends testNewUser
     */
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

    /**
     * @group user
     * @depends testGUID
     */
    public function testID()
    {
        $user = new User();
        $this->assertTrue($user->register());
        // 应该分配了 ID。
        $this->assertNotEmpty($user->id);
        $idAttribute = $user->idAttribute;
        // 直接访问 ID 和 通过 ID 属性名称访问的 ID 应该一致。
        $this->assertEquals($user->id, $user->$idAttribute);
        $this->assertTrue($user->deregister());
        
        // 预分配 ID。
        $user = new User(['idPreassigned' => true, 'id' => 123456]);
        $this->assertTrue($user->register());
        
        // 预分配的 ID 应该是 123456，不能是其他值。
        $this->assertEquals(123456, $user->id);
        
        // 通过 ID 应该能查找到用户。
        $user = User::find()->id(123456, 'like')->one();
        
        // 且可以注销。
        $this->assertTrue($user->deregister());
        
        // ID 只能是数字，因此赋值字母肯定会出错。
        $user = new User(['idPreassigned' => true, 'id' => 'abcdefg']);
        $this->assertNotNull($user->register());
        $this->assertNotEmpty($user->errors);
        
        // 如果不将 ID 设为预分配，则直接赋值依然会忽略。
        $user = new User(['id' => 123456]);
        $this->assertTrue($user->register());
        $this->assertNotEquals(123456, $user->id);
        $this->assertTrue($user->deregister());
    }
    
    /**
     * @group user
     * @depends testID
     */
    public function testIP()
    {
        $ipAddress = '::1';
        $user = new User(['enableIP' => User::$ipAll, 'ipAddress' => $ipAddress]);
        $this->assertEquals($ipAddress, $user->ipAddress);
        $this->assertTrue($user->register());
        $this->assertEquals(User::$ipAll, $user->enableIP);
        $this->assertEquals($ipAddress, $user->ipAddress);
        $ipTypeAttribute = $user->ipTypeAttribute;
        $this->assertEquals(Ip::IPv6, $user->$ipTypeAttribute);
        $this->assertTrue($user->deregister());

        $user = new User(['enableIP' => User::$ipv4, 'ipAddress' => $ipAddress]);
        $this->assertTrue($user->register());
        $this->assertEquals(User::$ipv4, $user->enableIP);
        $this->assertEquals(0, $user->ipAddress);
        $this->assertTrue($user->deregister());
        
        $user = new User(['enableIP' => User::$ipv6, 'ipAddress' => $ipAddress]);
        $this->assertTrue($user->register());
        $this->assertEquals(User::$ipv6, $user->enableIP);
        $this->assertEquals($ipAddress, $user->ipAddress);
        $this->assertTrue($user->deregister());
        
        $ipAddress = '127.0.0.1';
        $user = new User(['enableIP' => User::$ipAll, 'ipAddress' => $ipAddress]);
        $this->assertTrue($user->register());
        $this->assertEquals(User::$ipAll, $user->enableIP);
        $this->assertEquals($ipAddress, $user->ipAddress);
        $ipTypeAttribute = $user->ipTypeAttribute;
        $this->assertEquals(Ip::IPv4, $user->$ipTypeAttribute);
        $this->assertTrue($user->deregister());
        
        $user = new User(['enableIP' => User::$ipv4, 'ipAddress' => $ipAddress]);
        $this->assertTrue($user->register());
        $this->assertEquals(User::$ipv4, $user->enableIP);
        $this->assertEquals($ipAddress, $user->ipAddress);
        $this->assertTrue($user->deregister());
        
        $user = new User(['enableIP' => User::$ipv6, 'ipAddress' => $ipAddress]);
        $this->assertTrue($user->register());
        $this->assertEquals(User::$ipv6, $user->enableIP);
        $this->assertEquals(0, $user->ipAddress);
        $this->assertTrue($user->deregister());
    }
    
    /**
     * @group user
     * @depends testIP
     */
    public function testPassword()
    {
        $password = '123456';
        $user = new User();
        $this->assertTrue($user->hasEventHandlers(User::$eventAfterSetPassword));
        $this->assertFalse($user->hasEventHandlers(User::$eventBeforeValidatePassword));
        $user->on(User::$eventAfterSetPassword, function($event) {
            $this->assertTrue(true, 'EVENT_AFTER_SET_PASSWORD');
            $sender = $event->sender;
            $this->assertInstanceOf(User::className(), $sender);
        });
        $this->assertTrue($user->hasEventHandlers(User::$eventAfterSetPassword));
        $user->on(User::$eventBeforeValidatePassword, function($event) {
            $this->assertTrue(true, 'EVENT_BEFORE_VALIDATE_PASSWORD');
            $sender = $event->sender;
            $this->assertInstanceOf(User::className(), $sender);
        });
        $this->assertTrue($user->hasEventHandlers(User::$eventBeforeValidatePassword));
        $user->password = $password;
        $passwordHashAttribute = $user->passwordHashAttribute;
        $this->assertTrue($this->validatePassword($password, $user->$passwordHashAttribute));
        $this->assertFalse($this->validatePassword($password . ' ', $user->$passwordHashAttribute));
    }
    
    public function onResetPasswordFailed($event)
    {
        $sender = $event->sender;
        var_dump($sender->errors);
        $this->assertFalse(true);
    }   
    
    private function validatePassword($password, $hash)
    {
        return Yii::$app->security->validatePassword($password, $hash);
    }
}