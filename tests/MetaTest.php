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
use rhosocial\base\models\tests\data\ar\Meta;

/**
 * @author vistart <i@vistart.me>
 */
class MetaTest extends TestCase
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
     */
    public function testNew()
    {
        $user = $this->prepareUser();
        $key = \Yii::$app->security->generateRandomString();
        $value = \Yii::$app->security->generateRandomString();
        $this->assertTrue(Meta::set($key, $value, $user->guid));
        $this->assertNull(Meta::get($key . \Yii::$app->security->generateRandomString(1)));
        $this->assertEquals($value, Meta::get($key));
        $this->assertEquals($value, Meta::gets()[$key]);
        $this->assertEquals($value, Meta::gets([$key])[$key]);
        $value = \Yii::$app->security->generateRandomString();
        Meta::sets(null);
        Meta::sets([$key => $value]);
        $this->assertEquals($value, Meta::gets([$key])[$key]);
        $this->assertEquals(1, Meta::set($key));
        $this->assertEquals(0, Meta::remove($key));
        $this->assertNull(Meta::get($key));
        $this->assertTrue($user->deregister());
    }
}