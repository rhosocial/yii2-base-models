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
use rhosocial\base\models\tests\data\ar\RedisBlameable;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class RedisBlameableTest extends TestCase
{
    public static function prepareUser()
    {
        $user = new User(['password' => '123456']);
        $user->register();
        return $user;
    }
    
    /**
     * @group redis
     * @group blameable
     */
    public function testNew()
    {
        $user = static::prepareUser();
        $content = (string)mt_rand(1, 65535);
        $blameable = $user->create(RedisBlameable::class, ['content' => $content]);
        if ($blameable->save()) {
            $this->assertTrue(true);
        } else {
            var_dump($blameable->errors);
            $this->fail();
        }
        $blameable = RedisBlameable::findByIdentity($user)->one();
        $this->assertInstanceOf(RedisBlameable::class, $blameable);
        $this->assertEquals($content, $blameable->content);
        $this->assertEquals(1, $blameable->delete());
        $this->assertNull(RedisBlameable::findByIdentity($user)->one());
        $this->assertTrue($user->deregister());
    }
}