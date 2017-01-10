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
    
    public function passwordProvider()
    {
        for ($i = 0; $i < 3; $i++) {
            yield [$this->faker->password($this->faker->randomElement([1, 2, 3, 4, 5, 6]), $this->faker->randomElement([16, 17, 18, 19, 20, 21]))];
        }
    }
}