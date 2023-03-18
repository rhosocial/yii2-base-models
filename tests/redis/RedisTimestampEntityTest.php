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

namespace rhosocial\base\models\tests\redis;

use rhosocial\base\models\tests\data\ar\redis\TimestampEntity;
use rhosocial\base\models\tests\data\ar\redis\ExpiredTimestampEntity;
use yii\base\Exception;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class RedisTimestampEntityTest extends RedisEntityTestCase
{
    protected function setUp() : void {
        parent::setUp();
        $this->entity = new TimestampEntity();
    }

    protected function tearDown() : void {
        TimestampEntity::deleteAll();
        ExpiredTimestampEntity::deleteAll();
        parent::tearDown();
    }

    /**
     * @group redis
     * @group entity
     * @group timestamp
     * @throws Exception
     */
    public function testNew()
    {
        $this->assertTrue($this->entity->save());
        $this->assertEquals($this->entity->getCreatedAt(), $this->entity->getUpdatedAt());
        $this->assertNotEmpty($this->entity->getCreatedAt());
        $this->assertFalse($this->entity->hasEverBeenEdited());
        
        sleep(1);
        $this->entity->content = \Yii::$app->security->generateRandomString();
        $this->assertTrue($this->entity->save());
        $this->assertNotEquals($this->entity->getCreatedAt(), $this->entity->getUpdatedAt());
        $this->assertNotEmpty($this->entity->getUpdatedAt());
        $this->assertTrue($this->entity->hasEverBeenEdited());
        $this->assertGreaterThanOrEqual(1, $this->entity->delete());
    }

    /**
     * @group redis
     * @group entity
     * @group timestamp
     */
    public function testCreatedAt()
    {
        $this->assertTrue($this->entity->save());
        $this->assertEquals(time(), $this->entity->getCreatedAt());
        $this->assertGreaterThanOrEqual(1, $this->entity->delete());
    }

    /**
     * @group redis
     * @group entity
     * @group timestamp
     */
    public function testUpdatedAt()
    {
        $this->assertTrue($this->entity->save());
        $this->assertEquals(time(), $this->entity->getUpdatedAt());
        $this->assertGreaterThanOrEqual(1, $this->entity->delete());
    }

    /**
     * @group redis
     * @group entity
     * @group timestamp
     * @depends testCreatedAt
     */
    public function testFindByCreatedAt()
    {
        $this->assertTrue($this->entity->save());
        $entities = TimestampEntity::find()->createdAt(time() - 1, time())->all();
        $this->assertCount(1, $entities);
        $this->assertGreaterThanOrEqual(1, $this->entity->delete());
    }

    /**
     * @group redis
     * @group entity
     * @group timestamp
     * @depends testUpdatedAt
     */
    public function testFindByUpdatedAt()
    {
        $this->assertTrue($this->entity->save());
        $entities = TimestampEntity::find()->updatedAt(time() - 1, time())->all();
        $this->assertCount(1, $entities);
        $this->assertGreaterThanOrEqual(1, $this->entity->delete());
    }

    /**
     * @group redis
     * @group entity
     * @group timestamp
     * @throws Exception
     */
    public function testHasEverBeenEdited()
    {
        $this->assertTrue($this->entity->save());
        $this->assertFalse($this->entity->hasEverBeenEdited());
        $createdAt = $this->entity->getCreatedAt();
        $updatedAt = $this->entity->getUpdatedAt();
        $this->entity = TimestampEntity::findOne((string)($this->entity));
        sleep(1);
        $this->entity->content = \Yii::$app->security->generateRandomString();
        $this->assertTrue($this->entity->save());
        $this->entity = TimestampEntity::findOne((string)($this->entity));
        $this->assertEquals($createdAt, $this->entity->getCreatedAt());
        $this->assertNotEquals($updatedAt, $this->entity->getUpdatedAt());
        $this->assertTrue($this->entity->hasEverBeenEdited());
        $this->assertGreaterThanOrEqual(1, $this->entity->delete());
    }

    /**
     * @group redis
     * @group entity
     * @group timestamp
     */
    public function testIsExpired()
    {
        $this->entity = new ExpiredTimestampEntity(['expiredAfter' => 1]);
        $this->assertEquals(1, $this->entity->getExpiredAfter());
        $this->assertTrue($this->entity->save());
        sleep(2);
        $this->entity = ExpiredTimestampEntity::findOne((string)($this->entity));
        //$this->assertNull($this->entity);
    }

    /**
     * @group redis
     * @group entity
     * @group timestamp
     */
    public function testInitTimestamp()
    {
        $this->assertTrue($this->entity->save());
        $this->assertGreaterThanOrEqual(1, $this->entity->delete());
    }
}
