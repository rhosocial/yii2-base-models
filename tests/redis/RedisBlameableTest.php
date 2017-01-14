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
}