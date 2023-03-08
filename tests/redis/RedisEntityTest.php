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
use rhosocial\base\models\tests\data\ar\RedisEntity;
use rhosocial\base\models\tests\data\ar\GUIDRedisEntity;
use yii\base\Exception;
use yii\db\StaleObjectException;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class RedisEntityTest extends RedisEntityTestCase
{
    /**
     * @group redis
     * @group entity
     * @param integer $severalTimes
     * @throws StaleObjectException
     * @throws \yii\db\Exception
     * @dataProvider severalTimes
     */
    public function testNew(int $severalTimes)
    {
        $this->assertTrue($this->entity->save());
        $this->assertGreaterThanOrEqual(1, $this->entity->delete());
    }
    
    /**
     * @group redis
     * @group entity
     * @param int $severalTimes
     * @dataProvider severalTimes
     */
    public function testPrimaryKey(int $severalTimes)
    {
        $this->assertEquals([$this->entity->idAttribute], RedisEntity::primaryKey());
        
        $this->entity = new GUIDRedisEntity();
        $this->assertEquals([$this->entity->guidAttribute], GUIDRedisEntity::primaryKey());
    }

    /**
     * @group redis
     * @group entity
     * @param int $severalTimes
     * @throws StaleObjectException
     * @throws \yii\db\Exception
     * @dataProvider severalTimes
     */
    public function testCreatedAt(int $severalTimes)
    {
        $this->assertTrue($this->entity->save());
        $this->assertEquals(gmdate('Y-m-d H:i:s'), $this->entity->getCreatedAt());
        $this->assertGreaterThanOrEqual(1, $this->entity->delete());
    }

    /**
     * @group redis
     * @group entity
     * @param int $severalTimes
     * @throws StaleObjectException
     * @throws \yii\db\Exception
     * @dataProvider severalTimes
     */
    public function testUpdatedAt(int $severalTimes)
    {
        $this->assertTrue($this->entity->save());
        $this->assertEquals(gmdate('Y-m-d H:i:s'), $this->entity->getUpdatedAt());
        $this->assertGreaterThanOrEqual(1, $this->entity->delete());
    }

    /**
     * @group redis
     * @group entity
     * @depends      testCreatedAt
     * @param int $severalTimes
     * @throws StaleObjectException
     * @throws \yii\db\Exception
     * @dataProvider severalTimes
     */
    public function testFindByCreatedAt(int $severalTimes)
    {
        $this->assertTrue($this->entity->save());
        $entities = RedisEntity::find()->createdAt(gmdate('Y-m-d H:i:s'), gmdate('Y-m-d H:i:s'))->all();
        $this->assertCount(1, $entities);
        $this->assertGreaterThanOrEqual(1, $this->entity->delete());
    }

    /**
     * @group redis
     * @group entity
     * @depends      testUpdatedAt
     * @param int $severalTimes
     * @throws StaleObjectException
     * @throws \yii\db\Exception
     * @dataProvider severalTimes
     */
    public function testFindByUpdatedAt(int $severalTimes)
    {
        $this->assertTrue($this->entity->save());
        $entities = RedisEntity::find()->updatedAt(gmdate('Y-m-d H:i:s'), gmdate('Y-m-d H:i:s'))->all();
        $this->assertCount(1, $entities);
        $this->assertGreaterThanOrEqual(1, $this->entity->delete());
    }

    /**
     * @group redis
     * @group entity
     * @param int $severalTimes
     * @throws \yii\db\Exception
     * @throws StaleObjectException
     * @dataProvider severalTimes
     */
    public function testFindFailed(int $severalTimes)
    {
        $this->assertTrue($this->entity->save());
        try {
            $entities = RedisEntity::find()->updatedAt(gmdate('Y-m-d H:i:s'))->all();
            $this->fail();
        } catch (\Exception $ex)
        {
            $this->assertInstanceOf(\yii\db\Exception::class, $ex);
        }
        $this->assertGreaterThanOrEqual(1, $this->entity->delete());
    }

    /**
     * @group redis
     * @group entity
     * @group timestamp
     * @param int $severalTimes
     * @dataProvider severalTimes
     * @throws Exception
     */
    public function testHasEverBeenEdited(int $severalTimes)
    {
        $this->assertTrue($this->entity->save());
        $this->assertFalse($this->entity->hasEverBeenEdited());
        $createdAt = $this->entity->getCreatedAt();
        $updatedAt = $this->entity->getUpdatedAt();
        $this->entity = RedisEntity::findOne((string)($this->entity));
        sleep(1);
        $this->entity->content = (\Yii::$app->security->generateRandomString());
        $this->assertTrue($this->entity->save());
        $this->entity = RedisEntity::findOne((string)($this->entity));
        $this->assertEquals($createdAt, $this->entity->getCreatedAt());
        $this->assertNotEquals($updatedAt, $this->entity->getUpdatedAt());
        $this->assertTrue($this->entity->hasEverBeenEdited());
        $this->assertGreaterThanOrEqual(1, $this->entity->delete());
    }
    
    public function severalTimes(): \Generator
    {
        for ($i = 0; $i < 3; $i++)
        {
            yield [$i];
        }
    }
}