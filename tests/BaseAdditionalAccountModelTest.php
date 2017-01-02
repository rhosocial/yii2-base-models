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
    /**
     * 准备一个用户模型，密码是‘123456’。
     * 这个用户模型在注册时会同时注册一个“额外账户”模型。
     * 额外账户模型的账户内容（content）应当与通过用户链式访问方式获得的内容一致。
     * 默认没有独立密码，也不能用于登录。
     * @return User
     */
    private function prepareUser()
    {
        $user = new User(['password' => '123456']);
        $aa = $this->prepareModel($user);
        $content = $aa->content;
        $user->register([$aa]);
        $this->assertEquals($content, $user->additionalAccounts[0]->content);
        return $user;
    }
    
    /**
     * 准备一个“额外账户”模型。
     * 该模型通过已知用户创建。
     * @param User $user
     * @param array $config
     * @return AdditionalAccount
     */
    private function prepareModel($user, $config = ['content' => 0])
    {
        $aa = $user->create(AdditionalAccount::class, $config);
        return $aa;
    }
    
    /**
     * 该测试用例主要测试“用户”模型以及通过该用户模型创建的“额外账户”模型，并尝试
     * 注册。
     * @group user
     */
    public function testInit()
    {
        $user = new User(['password' => '123456']);
        $aa = $user->create(AdditionalAccount::class, ['content' => 0]);
        
        // 必须返回一个“额外账户”模型。
        $this->assertNotNull($aa);
        
        // 该用户 GUID 应当与“额外账户”模型的所属用户的 GUID 值一致。
        // 因为该用户还未注册（未保存到数据库），故不能用链式访问。
        $this->assertEquals($user->guid, $aa->{$aa->createdByAttribute});
        
        // 的账户类型是 0，代表自发创建（默认值）。
        $this->assertEquals(0, $aa->content);
        
        // “额外账户”的所有属性都应当通过规则验证。
        $this->assertTrue($aa->validate());
        $result = $user->register([$aa]);
        if ($result === true) {
            $this->assertTrue($result);
        } else {
            var_dump($aa->errors);
            $this->fail();
        }
        
        // 当前用户拥有的额外账户模型应当只有一个。
        $this->assertEquals(1, $aa->countOfOwner());
        
        // 测试完毕，注销账户。
        $this->assertTrue($user->deregister());
    }
    
    /**
     * 测试无独立密码。
     * @group user
     * @depends testInit
     */
    public function testNonPassword()
    {
        $user = $this->prepareUser();
        $aa = $user->additionalAccounts[0];
        
        // 默认不启用独立密码
        $this->assertFalse($aa->independentPassword);
        $this->assertTrue($user->deregister());
    }
    /**
     * 测试独立密码
     * @group user
     * @depends testNonPassword
     */
    public function testPassword()
    {
        $user = $this->prepareUser();
        $aa = $user->additionalAccounts[0];
        // 删除原来的“额外账户”模型。
        $aa->delete();
        // 新建允许独立密码的额外账户。
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
     * 测试禁用登录
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
     * 测试启用登录
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