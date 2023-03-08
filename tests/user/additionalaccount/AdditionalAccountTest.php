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

namespace rhosocial\base\models\tests\user\additionalaccount;

use rhosocial\base\models\tests\data\ar\User;
use rhosocial\base\models\tests\data\ar\AdditionalAccount;
use rhosocial\base\models\tests\user\UserTestCase;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class AdditionalAccountTest extends UserTestCase
{
    /**
     * @group user
     * @group additional-account
     * @group registration
     */
    public function testInit()
    {
        $user = new User(['password' => '123456']);

        $aa = $user->create(AdditionalAccount::class, ['source' => 0]);

        // 必须返回一个“额外账户”模型。
        $this->assertNotNull($aa);

        // 该用户 GUID 应当与“额外账户”模型的所属用户的 GUID 值一致。
        // 因为该用户还未注册（未保存到数据库），故不能用链式访问。
        $this->assertEquals($user->getGUID(), $aa->{$aa->createdByAttribute});

        // 的账户类型是 0，代表自发创建（默认值）。
        $this->assertEquals(0, $aa->content);

        // “额外账户”的所有属性都应当通过规则验证。
        $this->assertTrue($aa->validate());
        $result = $user->register([$aa]);
        if ($result === true) {
            $this->assertTrue($result);
        } else {
            var_dump($result->getMessage());
            var_dump($aa->errors);
            $this->fail();
        }

        // 当前用户拥有的额外账户模型应当只有一个。
        $this->assertEquals(1, $aa->countOfOwner());

        // 测试完毕，注销账户。
        $this->assertTrue($user->deregister());
    }

    /**
     * @group user
     * @group additional-account
     */
    public function testSeparatePassword()
    {
        $this->user = new User(['password' => '123456']);
        $aa = $this->user->create(AdditionalAccount::class, ['source' => 0, 'password' => $this->faker->randomLetter(), 'separateLogin' => true]);
        /* @var $aa AdditionalAccount */
        $this->assertTrue($this->user->register([$aa]));
        $this->assertInstanceOf(AdditionalAccount::class, $this->user->additionalAccounts[0]);
        $aa = $this->user->additionalAccounts[0];
        $this->assertFalse($aa->getIsEmptyPassword());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group additional-account
     */
    public function testNotSeparatePassword()
    {
        $this->user = new User(['password' => '123456']);
        $aa = $this->user->create(AdditionalAccount::class, ['source' => 0]);
        /* @var $aa AdditionalAccount */
        $aa->setEmptyPassword();
        $this->assertTrue($this->user->register([$aa]));
        $this->assertInstanceOf(AdditionalAccount::class, $this->user->additionalAccounts[0]);
        $aa = $this->user->additionalAccounts[0];
        $this->assertTrue($aa->getIsEmptyPassword());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group additional-account
     */
    public function testSeparateLogin()
    {
        $this->user = new User(['password' => '123456']);
        $aa = $this->user->create(AdditionalAccount::class, ['source' => 0, 'separateLogin' => true]);
        /* @var $aa AdditionalAccount */
        $aa->setEmptyPassword();
        $this->assertTrue($this->user->register([$aa]));

        $this->assertInstanceOf(AdditionalAccount::class, $this->user->additionalAccounts[0]);
        $aa = $this->user->additionalAccounts[0];
        $this->assertTrue($aa->separateLogin);

        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group additional-account
     */
    public function testNotSeparateLogin()
    {
        $password = '123456';
        $this->user = new User(['password' => $password]);
        $aa = $this->user->create(AdditionalAccount::class, ['source' => 0, 'separateLogin' => false]);
        /* @var $aa AdditionalAccount */
        $aa->setEmptyPassword();
        $this->assertTrue($this->user->register([$aa]));

        $this->assertInstanceOf(AdditionalAccount::class, $this->user->additionalAccounts[0]);
        $aa = $this->user->additionalAccounts[0];
        $this->assertFalse($aa->separateLogin);

        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group additional-account
     */
    public function testSeparateLoginAttribute()
    {
        $password = '123456';
        $this->user = new User(['password' => $password]);
        $aa = $this->user->create(AdditionalAccount::class, ['source' => 0, 'separateLoginAttribute' => false]);
        /* @var $aa AdditionalAccount */
        $this->assertFalse($aa->separateLoginAttribute);
        $this->assertTrue($this->user->register([$aa]));
        $this->assertFalse($aa->getSeparateLogin());
        $aa->setSeparateLogin(true);
        $this->assertFalse($aa->getSeparateLogin());
        $aa->setSeparateLogin(false);
        $this->assertFalse($aa->getSeparateLogin());
        $this->assertTrue($this->user->deregister());
    }
}
