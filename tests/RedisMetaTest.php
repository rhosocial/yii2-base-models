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
use rhosocial\base\models\tests\data\ar\RedisMeta;

/**
 * @author vistart <i@vistart.me>
 */
class RedisMetaTest extends TestCase
{

    /**
     * 
     * @return User
     */
    private function prepareUser()
    {
        $user = new User(['password' => '123456']);
        $this->assertTrue($user->register());
        return $user;
    }
    /**
     * @group meta
     * @group redis
     */
    public function testNew()
    {
        $user = $this->prepareUser();
        $key = \Yii::$app->security->generateRandomString();
        $value = \Yii::$app->security->generateRandomString();
        $this->assertTrue(RedisMeta::set($key, $value, $user->guid));
        $this->assertNull(RedisMeta::get($key . \Yii::$app->security->generateRandomString(1)));
        $this->assertEquals($value, RedisMeta::get($key));
        $this->assertEquals($value, RedisMeta::gets()[$key]);
        $this->assertEquals($value, RedisMeta::gets([$key])[$key]);
        $value = \Yii::$app->security->generateRandomString();
        RedisMeta::sets(null);
        RedisMeta::sets([$key => $value]);
        $this->assertEquals($value, RedisMeta::gets([$key])[$key]);
        $this->assertEquals(1, RedisMeta::set($key));
        $this->assertEquals(0, RedisMeta::remove($key));
        $this->assertNull(RedisMeta::get($key));
        $this->assertTrue($user->deregister());
    }
}