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
        // 此时不应该为 null
        $this->assertNotNull($email);
        
        $user = new User();
        $email = $user->findOneOrCreate(UserEmail::class, ['email' => 'i@vistart.me', 'type' => UserEmail::TYPE_HOME]);
        
        $this->assertNotNull($email);
        
        $this->assertTrue($user->register([$email]));
        
        $user = User::findOne($user->guid);
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(UserEmail::class, $email);
        
        $this->assertEquals(1, $email->countOfOwner());
        
        $email_guid = $user->emails[0]->guid;
        $email = $user->findOneOrCreate(UserEmail::class);
        $this->assertEquals($email_guid, $email->guid);
        
        $email = $user->findOneOrCreate(UserEmail::class, [$email->guidAttribute, $email_guid]);
        $this->assertEquals($email_guid, $email->guid);
        
        $guid = $user->guid;
        
        $this->assertTrue($user->deregister());
        $user = User::findOne($guid);
        $email = UserEmail::findOne(['user_guid' => $guid]);
        
        $this->assertNull($user);
        $this->assertNull($email);
    }
    
    /**
     * @depends testNew
     * @group email
     * @group blameable
     */
    public function testCreatorandUpdater()
    {
        $user = new User();
        $email = $user->findOneOrCreate(UserEmail::class, ['email' => 'i@vistart.me', 'type' => UserEmail::TYPE_HOME]);
        // 此时不应该为 null
        $this->assertNotNull($email);
        // 与用户一同注册
        $this->assertTrue($user->register([$email]));
        $this->assertNotNull($email->user);
        $this->assertNull($email->updater);
        // 此处应该注销成功。
        $this->assertTrue($user->deregister());
    }
}