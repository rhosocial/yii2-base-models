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

namespace rhosocial\base\models\tests\redis;

use rhosocial\base\models\tests\data\ar\RedisEntity;
use rhosocial\base\models\tests\data\ar\GUIDRedisEntity;

/**
 * @author vistart <i@vistart.me>
 */
class RedisEntityTest extends RedisEntityTestCase
{
    /**
     * @group redis
     * @group entity
     */
    public function testNew()
    {
        $this->assertTrue($this->entity->save());
        $this->assertGreaterThanOrEqual(1, $this->entity->delete());
    }
    
    /**
     * @group redis
     * @group entity
     */
    public function testPrimaryKey()
    {
        $this->assertEquals([$this->entity->idAttribute], RedisEntity::primaryKey());
        
        $this->entity = new GUIDRedisEntity();
        $this->assertEquals([$this->entity->guidAttribute], GUIDRedisEntity::primaryKey());
    }
}