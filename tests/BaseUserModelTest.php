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

namespace rhosocial\base\models\tests;

use rhosocial\base\models\tests\data\ar\User;
use rhosocial\base\helpers\Number;
use rhosocial\base\helpers\IP;
use Yii;
use yii\db\IntegrityException;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class BaseUserModelTest extends TestCase
{
    /**
     * @group user
     */
    public function testInit()
    {
        $users = [];
        for ($i = 0; $i < 1000; $i++) {
            $users[] = new User();
        }
        $failed = 0;
        foreach ($users as $key => $user) {
            if ($user->register() !== true) {
                unset($users[$key]);
                $failed++;
                //echo $user->readableGUID . ' | ' . $user->id . (empty($user->getErrors()) ? '' : ' | ' . print_r(current($user->getErrors()))) . "\n";
            } else {
            }
        }
        
        $count = count($users);
        $this->assertEquals(1000 - $failed, $count, "100 users should be registered.");
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
        $this->assertTrue($user->unregister());
        
        // 预分配 ID。
        $user = new User(['idPreassigned' => true, 'id' => 123456]);
        $this->assertTrue($user->register());
        
        // 预分配的 ID 应该是 123456，不能是其他值。
        $this->assertEquals(123456, $user->id);
        
        // 通过 ID 应该能查找到用户。
        $user = User::find()->id(123456, 'like')->one();
        
        // 且可以注销。
        $this->assertTrue($user->unregister());
        
        // ID 只能是数字，因此赋值字母肯定会出错。
        $user = new User(['idPreassigned' => true, 'id' => 'abcdefg']);
        $this->assertNotNull($user->register());
        $this->assertNotEmpty($user->errors);
        
        // 如果不将 ID 设为预分配，则直接赋值依然会忽略。
        $user = new User(['id' => 123456]);
        $this->assertTrue($user->register());
        $this->assertNotEquals(123456, $user->id);
        $this->assertTrue($user->unregister());
    }
    
    /**
     * @group user
     * @depends testID
     */
    public function testIP()
    {
        $ipAddress = '::1';
        $user = new User(['enableIP' => User::IP_ALL_ENABLED, 'ipAddress' => $ipAddress]);
        $this->assertEquals($ipAddress, $user->ipAddress);
        $this->assertTrue($user->register());
        $this->assertEquals(User::IP_ALL_ENABLED, $user->enableIP);
        $this->assertEquals($ipAddress, $user->ipAddress);
        $ipTypeAttribute = $user->ipTypeAttribute;
        $this->assertEquals(IP::IPv6, $user->$ipTypeAttribute);
        $this->assertTrue($user->deregister());

        $user = new User(['enableIP' => User::IP_V4_ENABLED, 'ipAddress' => $ipAddress]);
        $this->assertTrue($user->register());
        $this->assertEquals(User::IP_V4_ENABLED, $user->enableIP);
        $this->assertEquals(0, $user->ipAddress);
        $this->assertTrue($user->deregister());
        
        $user = new User(['enableIP' => User::IP_V6_ENABLED, 'ipAddress' => $ipAddress]);
        $this->assertTrue($user->register());
        $this->assertEquals(User::IP_V6_ENABLED, $user->enableIP);
        $this->assertEquals($ipAddress, $user->ipAddress);
        $this->assertTrue($user->deregister());
        
        $ipAddress = '127.0.0.1';
        $user = new User(['enableIP' => User::IP_ALL_ENABLED, 'ipAddress' => $ipAddress]);
        $this->assertTrue($user->register());
        $this->assertEquals(User::IP_ALL_ENABLED, $user->enableIP);
        $this->assertEquals($ipAddress, $user->ipAddress);
        $ipTypeAttribute = $user->ipTypeAttribute;
        $this->assertEquals(IP::IPv4, $user->$ipTypeAttribute);
        $this->assertTrue($user->deregister());
        
        $user = new User(['enableIP' => User::IP_V4_ENABLED, 'ipAddress' => $ipAddress]);
        $this->assertTrue($user->register());
        $this->assertEquals(User::IP_V4_ENABLED, $user->enableIP);
        $this->assertEquals($ipAddress, $user->ipAddress);
        $this->assertTrue($user->deregister());
        
        $user = new User(['enableIP' => User::IP_V6_ENABLED, 'ipAddress' => $ipAddress]);
        $this->assertTrue($user->register());
        $this->assertEquals(User::IP_V6_ENABLED, $user->enableIP);
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
        $this->assertTrue($user->hasEventHandlers(User::EVENT_AFTER_SET_PASSWORD));
        $this->assertFalse($user->hasEventHandlers(User::EVENT_BEFORE_VALIDATE_PASSWORD));
        $user->on(User::EVENT_AFTER_SET_PASSWORD, function($event) {
            $this->assertTrue(true, 'EVENT_AFTER_SET_PASSWORD');
            $sender = $event->sender;
            $this->assertInstanceOf(User::class, $sender);
        });
        $this->assertTrue($user->hasEventHandlers(User::EVENT_AFTER_SET_PASSWORD));
        $user->on(User::EVENT_BEFORE_VALIDATE_PASSWORD, function($event) {
            $this->assertTrue(true, 'EVENT_BEFORE_VALIDATE_PASSWORD');
            $sender = $event->sender;
            $this->assertInstanceOf(User::class, $sender);
        });
        $this->assertTrue($user->hasEventHandlers(User::EVENT_BEFORE_VALIDATE_PASSWORD));
        $user->password = $password;
        $passwordHashAttribute = $user->passwordHashAttribute;
        $this->assertTrue($this->validatePassword($password, $user->$passwordHashAttribute));
        $this->assertFalse($this->validatePassword($password . ' ', $user->$passwordHashAttribute));
    }
    
    public function onResetPasswordFailed($event)
    {
        $sender = $event->sender;
        var_dump($sender->errors);
        $this->fail();
    }   
    
    private function validatePassword($password, $hash): bool
    {
        return Yii::$app->security->validatePassword($password, $hash);
    }

    /**
     * @group user
     * @depends testPassword
     */
    public function testPasswordResetToken()
    {
        $password = '123456';
        $user = new User(['password' => $password]);
        $user->on(User::EVENT_RESET_PASSWORD_FAILED, [$this, 'onResetPasswordFailed']);
        $user->register();
        $this->assertTrue($user->applyForNewPassword());
        $password = $password . ' ';
        $passwordResetTokenAttribute = $user->passwordResetTokenAttribute;
        $user->resetPassword($password, $user->$passwordResetTokenAttribute);
        $user->deregister();
    }

    /**
     * @group user
     * @depends testPasswordResetToken
     */
    public function testStatus()
    {
        $user = new User();
        $guidAttribute = $user->guidAttribute;
        $guid = $user->guid;
        $this->assertTrue($user->register());
        $user = User::findOne($guid);
        $statusAttribute = $user->statusAttribute;
        $this->assertEquals(User::STATUS_ACTIVE, $user->$statusAttribute);
        $user = User::find()->where([$guidAttribute => $guid])->active(User::STATUS_INACTIVE)->one();
        $this->assertNull($user);
        $user = User::find()->where([$guidAttribute => $guid])->active(User::STATUS_ACTIVE)->one();
        $this->assertInstanceOf(User::class, $user);
        $this->assertTrue($user->deregister());
    }

    /**
     * @group user
     * @depends testStatus
     */
    public function testSource()
    {
        $user = new User();
        $guid = $user->guid;
        $guidAttribute = $user->guidAttribute;
        $this->assertTrue($user->register());
        $user = User::findOne($guid);
        $sourceAttribute = $user->sourceAttribute;
        $this->assertEquals(User::$sourceSelf, $user->$sourceAttribute);
        $user = User::find()->where([$guidAttribute => $guid])->source('1')->one();
        $this->assertNull($user);
        $user = User::find()->where([$guidAttribute => $guid])->source()->one();
        $this->assertInstanceOf(User::class, $user);
        $this->assertTrue($user->deregister());
    }

    /**
     * @group user
     * @depends testSource
     */
    public function testTimestamp()
    {
        $user = new User();
        $createdAtAttribute = $user->createdAtAttribute;
        $updatedAtAttribute = $user->updatedAtAttribute;
        $this->assertNull($user->$createdAtAttribute);
        $this->assertNull($user->$updatedAtAttribute);
        $result = $user->register();
        $this->assertTrue($result);
        $this->assertNotNull($user->$createdAtAttribute);
        $this->assertNotNull($user->$updatedAtAttribute);
        $this->assertTrue($user->deregister());
    }
    public $beforeRegisterEvent = '';
    public $afterRegisterEvent = '';
    public $beforeDeregisterEvent = '';
    public $afterDeregisterEvent = '';

    /**
     * @group user
     * @depends testTimestamp
     */
    public function testRegister()
    {
        $user = new User();
        $user->on(User::EVENT_BEFORE_REGISTER, [$this, 'onBeforeRegister']);
        $user->on(User::EVENT_AFTER_REGISTER, [$this, 'onAfterRegister']);
        $this->assertTrue($user->register());
        $this->assertEquals('beforeRegister', $this->beforeRegisterEvent);
        $this->assertEquals('afterRegister', $this->afterRegisterEvent);
        $authKeyAttribute = $user->authKeyAttribute;
        $this->assertEquals(40, strlen($user->$authKeyAttribute));
        $accessTokenAttribute = $user->accessTokenAttribute;
        $this->assertEquals(40, strlen($user->$accessTokenAttribute));
        $sourceAttribute = $user->sourceAttribute;
        $this->assertEquals(User::$sourceSelf, $user->$sourceAttribute);
        $statusAttribute = $user->statusAttribute;
        $this->assertEquals(User::STATUS_ACTIVE, $user->$statusAttribute);
        $user->on(User::EVENT_BEFORE_UNREGISTER, [$this, 'onBeforeDeregister']);
        $user->on(User::EVENT_AFTER_UNREGISTER, [$this, 'onAfterDeregister']);
        $this->assertTrue($user->deregister());
        $this->assertEquals('beforeDeregister', $this->beforeDeregisterEvent);
        $this->assertEquals('afterDeregister', $this->afterDeregisterEvent);
    }
    public function onBeforeRegister($event)
    {
        $sender = $event->sender;
        $this->assertInstanceOf(User::class, $sender);
        $this->beforeRegisterEvent = 'beforeRegister';
    }
    public function onAfterRegister($event)
    {
        $sender = $event->sender;
        $this->assertInstanceOf(User::class, $sender);
        $this->afterRegisterEvent = 'afterRegister';
    }
    public function onBeforeDeregister($event)
    {
        $sender = $event->sender;
        $this->assertInstanceOf(User::class, $sender);
        $this->beforeDeregisterEvent = 'beforeDeregister';
    }
    public function onAfterDeregister($event)
    {
        $sender = $event->sender;
        $this->assertInstanceOf(User::class, $sender);
        $this->afterDeregisterEvent = 'afterDeregister';
    }
    /**
     * @group user
     * @depends testRegister
     * @large
     */
    /*
    public function testNewUser256()
    {
        $users = [];
        for ($i = 0; $i < 256; $i++) {
            $password = '123456';
            $user = new User(['password' => $password]);
            $users[] = $user;
            if (!$user->register()) {
                $this->fail(($i + 1) . "\n" . $user->errors);
            }
        }
        foreach ($users as $key => $user) {
            if (!$user->deregister()) {
                $this->fail($key . "\n" . $user->errors);
            }
        }
        echo "$i\n";
    }
    /**
     * @group user
     * @depends testRegister
     */
    /*
    public function testCreateNonObject()
    {
        $user = new User();
        $this->assertNull($user->createProfile());
    }
    /**
     * @group user
     * @depends testCreateNonObject
     */
    /*
    public function testCreateCommentWithoutMap()
    {
        $user = new User(['password' => '123456']);
        $this->assertTrue($user->register());
        $comment = $user->createUserComment();
        $this->assertNull($comment);
        $this->assertTrue($user->deregister());
    }
    /**
     * @group user
     * @depends testCreateCommentWithoutMap
     */
    /*
    public function testCreateCommentWithMap()
    {
        $user = new User(['password' => '123456']);
        $this->assertTrue($user->register());
        $user->subsidiaryMap = [
            'Comment' => UserComment::class,
        ];
        $comment = $user->createComment(['class' => UserComment::class]);
        $this->assertInstanceOf(UserComment::class, $comment);
        $comment = $user->createSubsidiary(UserComment::class, ['class' => UserComment::class]);
        $this->assertTrue($user->deregister());
    }
    /**
     * @group user
     * @depends testCreateCommentWithMap
     */
    /*
    public function testNormalizeSubsidiaryClass()
    {
        $user = new User(['password' => '123456']);
        $this->assertNull($user->normalizeSubsidiaryClass(null));
    }*/
}