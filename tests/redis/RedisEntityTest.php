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
    
    /**
     * @group redis
     * @group entity
     */
    public function testCreatedAt()
    {
        $this->assertTrue($this->entity->save());
        $this->assertEquals(date('Y-m-d H:i:s'), $this->entity->getCreatedAt());
        $this->assertGreaterThanOrEqual(1, $this->entity->delete());
    }
    
    /**
     * @group redis
     * @group entity
     */
    public function testUpdatedAt()
    {
        $this->assertTrue($this->entity->save());
        $this->assertEquals(date('Y-m-d H:i:s'), $this->entity->getUpdatedAt());
        $this->assertGreaterThanOrEqual(1, $this->entity->delete());
    }
    
    /**
     * @group redis
     * @group entity
     * @depends testCreatedAt
     */
    public function testFindByCreatedAt()
    {
        $this->assertTrue($this->entity->save());
        $entities = RedisEntity::find()->createdAt(date('Y-m-d H:i:s'), date('Y-m-d H:i:s'))->all();
        $this->assertCount(1, $entities);
        $this->assertGreaterThanOrEqual(1, $this->entity->delete());
    }
    
    /**
     * @group redis
     * @group entity
     * @depends testUpdatedAt
     */
    public function testFindByUpdatedAt()
    {
        $this->assertTrue($this->entity->save());
        $entities = RedisEntity::find()->updatedAt(date('Y-m-d H:i:s'), date('Y-m-d H:i:s'))->all();
        $this->assertCount(1, $entities);
        $this->assertGreaterThanOrEqual(1, $this->entity->delete());
    }
    
    /**
     * @group redis
     * @group entity
     */
    public function testFindFailed()
    {
        $this->assertTrue($this->entity->save());
        try {
            $entities = RedisEntity::find()->updatedAt(date('Y-m-d H:i:s'))->all();
            $this->fail();
        } catch (\Exception $ex)
        {
            $this->assertInstanceOf(\yii\db\Exception::class, $ex);
        }
        $this->assertGreaterThanOrEqual(1, $this->entity->delete());
    }
}