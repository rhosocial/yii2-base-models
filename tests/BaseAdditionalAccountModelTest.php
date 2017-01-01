<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\tests;
use rhosocial\base\models\tests\data\ar\AdditionalAccount;
use rhosocial\base\models\tests\data\ar\User;

/**
 * Description of BaseAdditionalAccountModelTest
 *
 * @author vistart <i@vistart.me>
 */
class BaseAdditionalAccountModelTest extends TestCase
{
    private function prepareUser()
    {
        $user = new User(['password' => '123456']);
        $aa = $this->prepareModel($user);
        $content = $aa->content;
        $user->register([$aa]);
        $this->assertEquals($content, $user->additionalAccounts[0]->content);
        return $user;
    }
    
    private function prepareModel($user, $config = ['content' => 0])
    {
        $aa = $user->create(AdditionalAccount::class, $config);
        return $aa;
    }
    
    /**
     * @group user
     */
    public function testInit()
    {
        $user = new User(['password' => '123456']);
        $aa = $user->create(AdditionalAccount::class, ['content' => 0]);
        $this->assertNotNull($aa);
        $this->assertEquals($user->guid, $aa->{$aa->createdByAttribute});
        $this->assertEquals(0, $aa->content);
        $this->assertTrue($aa->validate());
        $result = $user->register([$aa]);
        if ($result === true) {
            $this->assertTrue($result);
        } else {
            var_dump($aa->errors);
            $this->fail();
        }
        $this->assertEquals(1, $aa->countOfOwner());
        $this->assertTrue($user->deregister());
    }
    
    /**
     * @group user
     * @depends testInit
     */
    public function testNonPassword()
    {
        $user = $this->prepareUser();
        $aa = $user->additionalAccounts[0];
        $this->assertFalse($aa->independentPassword);
        $this->assertTrue($user->deregister());
    }
    /**
     * @group user
     * @depends testNonPassword
     */
    public function testPassword()
    {
        $user = $this->prepareUser();
        $aa = $user->additionalAccounts[0];
        $aa->delete();
        $aa = $this->prepareModel($user, ['content' => 0, 'independentPassword' => true]);
        $this->assertTrue($aa->save());
        $aa->passwordHashAttribute = 'pass_hash';
        $aa->password = '123456';
        $result = $aa->save();
        if ($result) {
            $this->assertTrue($result);
        } else {
            var_dump($aa->errors);
            $this->fail();
        }
        $passwordHashAttribute = $aa->passwordHashAttribute;
        $this->assertStringStartsWith('$2y$' . $aa->passwordCost . '$', $aa->$passwordHashAttribute);
        $this->assertTrue($aa->validatePassword('123456'));
        $this->assertTrue($user->deregister());
    }
    /**
     * @depends testPassword
     */
    public function testDisableLogin()
    {
        $user = $this->prepareUser();
        $aa = $user->additionalAccounts[0];
        $this->assertFalse($aa->enableLoginAttribute);
        $this->assertTrue($user->deregister());
    }
    /**
     * @depends testDisableLogin
     */
    public function testEnableLogin()
    {
        $user = $this->prepareUser();
        $aa = $user->additionalAccounts[0];
        $aa->enableLoginAttribute = 'enable_login';
        $this->assertFalse($aa->canBeLogon);
        $aa->canBeLogon = true;
        $this->assertTrue($aa->canBeLogon);
        $enableLoginAttribute = $aa->enableLoginAttribute;
        $this->assertEquals(1, $aa->$enableLoginAttribute);
        $this->assertTrue($user->deregister());
    }
    /**
     * @depends testEnableLogin
     */
    public function testRules()
    {
        $user = $this->prepareUser();
        $aa = $user->additionalAccounts[0];
        $this->validateRules($aa->rules());
        $this->assertTrue($user->deregister());
    }
    private function AdditionalAccountRules()
    {
        return [
            [['guid'], 'required'],
            [['guid'], 'unique'],
            [['guid'], 'string', 'max' => 16],
        ];
    }
    private function validateRules($rules)
    {
        foreach ($rules as $key => $rule) {
            $this->assertTrue(is_array($rule));
            if (is_array($rule[0])) {
                
            } elseif (is_string($rule[0])) {
                
            } else {
                // 只可能是字符串或数组，不可能为其他类型。
                $this->assertTrue(false);
            }
        }
    }
}