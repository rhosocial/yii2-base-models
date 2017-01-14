<?php

/**
 *   _   __ __ _____ _____ ___  ____  _____
 *  | | / // // ___//_  _//   ||  __||_   _|
 *  | |/ // /(__  )  / / / /| || |     | |
 *  |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2017 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\tests\redis;

use rhosocial\base\models\tests\data\ar\RedisBlameable;

/**
 * @author vistart <i@vistart.me>
 */
class RedisBlameableTest extends RedisBlameableTestCase
{
    /**
     * @group redis
     * @group blameable
     */
    public function testNew()
    {
        $this->assertTrue($this->user->register([$this->blameable]));
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group redis
     * @group blameable
     */
    public function testFindByIdentity()
    {
        $this->assertTrue($this->user->register([$this->blameable]));
        $blameable = RedisBlameable::findByIdentity($this->user)->one();
        $this->assertInstanceOf(RedisBlameable::class, $blameable);
        $this->assertEquals(1, $blameable->delete());
        $nonExists = RedisBlameable::findByIdentity($this->user)->one();
        $this->assertNull($nonExists);
        $this->assertTrue($this->user->deregister());
    }
}