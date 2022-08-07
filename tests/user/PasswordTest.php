<?php

/**
 *   _   __ __ _____ _____ ___  ____  _____
 *  | | / // // ___//_  _//   ||  __||_   _|
 *  | |/ // /(__  )  / / / /| || |     | |
 *  |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2022 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\tests\user;

use rhosocial\base\models\tests\data\ar\User;

/**
 * @author vistart <i@vistart.me>
 */
class PasswordTest extends UserTestCase
{    
    /**
     * 测试用户注册再读取后的密码验证。
     * 正确情况下，validatePassword() 方法应该返回 true。
     * @group user
     * @group password
     * @group registration
     * @dataProvider passwordProvider
     */
    public function testAfterRegister($password)
    {
        $this->user->password = $password;
        $this->assertTrue($this->user->register());
        $guid = $this->user->getGUID();
        $this->user = User::findOne($guid);
        $this->assertTrue($this->user->validatePassword($password), 'Password: ' . $password);
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group user
     * @group password
     */
    public function testValidatePassword()
    {
        $password = \Yii::$app->security->generateRandomString();
        $this->user->password = $password;
        $this->assertTrue($this->user->validatePassword($password));
        
        $this->user->password = $password . '1';
        $this->assertFalse($this->user->validatePassword($password));
    }
    
    /**
     * @group user
     * @group password
     */
    public function testEmptyPassword()
    {
        $this->user->setEmptyPassword();
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->user->getIsEmptyPassword());
        $this->assertTrue($this->user->deregister());
    }
    
    public function passwordProvider()
    {
        for ($i = 0; $i < 3; $i++) {
            yield [$this->faker->password($this->faker->randomElement([1, 2, 3, 4, 5, 6]), $this->faker->randomElement([16, 17, 18, 19, 20, 21]))];
        }
    }
    
    /**
     * @group user
     * @group password
     */
    public function testPasswordRulesPass()
    {
        $this->user->setPasswordHashRules([
            [[$this->user->passwordHashAttribute], 'string', 'max' => $this->user->passwordHashAttributeLength + 1],
        ]);
        $this->user->setPassword();
        $this->assertTrue($this->user->validate());
    }
    
    /**
     * @group user
     * @group password
     */
    public function testPasswordRulesNotPass()
    {
        $this->user->setPasswordHashRules([
            [[$this->user->passwordHashAttribute], 'string', 'max' => $this->user->passwordHashAttributeLength - 1],
        ]);
        $this->user->setPassword();
        $this->assertFalse($this->user->validate());
    }
    
    /**
     * @group user
     * @group password
     */
    public function testPasswordResetTokenRulesNotPass()
    {
        $rules = [
            [[$this->user->passwordResetTokenAttribute], 'string', 'length' => 41],
        ];
        $this->user->setPasswordResetTokenRules($rules);
        
        $token = $this->user->setPasswordResetToken(sha1(\Yii::$app->security->generateRandomString()));
        
        $this->assertFalse($this->user->validate());
    }
    
    /**
     * @group user
     * @group password
     */
    public function testPasswordResetTokenRulesPass()
    {
        $rules = [
            [[$this->user->passwordResetTokenAttribute], 'string', 'max' => 41],
        ];
        $this->user->setPasswordResetTokenRules($rules);
        if ($this->user->validate()) {
            $this->assertTrue(true);
        } else {
            var_dump($this->user->getPasswordResetTokenRules());
            var_dump($this->user->getErrors());
        }
        $this->assertTrue($this->user->save());
    }
    
    /**
     * @group user
     * @group password
     */
    public function testApplyforNewPassword()
    {
        $this->assertFalse($this->user->applyForNewPassword());
        
        $this->assertTrue($this->user->register());
        $this->user->passwordResetTokenRules = null;
        $this->assertTrue($this->user->applyForNewPassword());
        
        $this->user->passwordResetTokenAttribute = 'password_reset_token';
        $this->assertTrue($this->user->applyForNewPassword());
        
        $token = $this->user->getPasswordResetToken();
        $this->assertNotNull($token);
        $this->assertFalse($this->user->resetPassword('', $token . '1'));
        
        $this->assertNotNull($this->user->getPasswordResetToken());
        
        $password = \Yii::$app->security->generateRandomString();
        $this->assertTrue($this->user->resetPassword($password, $token));
        
        $this->assertTrue($this->user->validatePassword($password));
    }
}