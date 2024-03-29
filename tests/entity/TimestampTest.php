<?php

/**
 *   _   __ __ _____ _____ ___  ____  _____
 *  | | / // // ___//_  _//   ||  __||_   _|
 *  | |/ // /(__  )  / / / /| || |     | |
 *  |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 -2023 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\tests\entity;

use rhosocial\base\models\tests\data\ar\Entity;
use rhosocial\base\models\tests\data\ar\EntityLocal;
use rhosocial\base\models\tests\data\ar\ExpiredEntity;
use rhosocial\base\models\tests\data\ar\ExpiredCallbackEntity;
use Throwable;
use yii\base\Exception;
use yii\db\StaleObjectException;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class TimestampTest extends EntityTestCase
{
    /**
     * @group entity
     * @group timestamp
     * @param int $severalTimes
     * @dataProvider severalTimes
     */
    public function testNotExpired(int $severalTimes)
    {
        $this->assertFalse($this->entity->expiredAfterAttribute);
        $this->assertFalse($this->entity->getIsExpired());
        $this->assertFalse($this->entity->setExpiredAfter(1));
    }

    /**
     * @group entity
     * @group timestamp
     * @param int $severalTimes
     * @throws StaleObjectException
     * @throws Throwable
     * @dataProvider severalTimes
     */
    public function testExpired(int $severalTimes)
    {
        $this->entity = new ExpiredEntity();
        $this->entity->setExpiredAfter(1);
        $this->assertEquals(1, $this->entity->getExpiredAfter());
        $this->assertTrue($this->entity->save());
        $createdAt = $this->entity->getCreatedAt();
        $this->assertNotNull($createdAt);
        $updatedAt = $this->entity->getUpdatedAt();
        $this->assertNotNull($updatedAt);
        sleep(2);
        $entity = ExpiredEntity::findOne($this->entity->getGUID());
        $this->assertEquals($createdAt, $entity->getCreatedAt());
        $this->assertEquals($updatedAt, $entity->getUpdatedAt());
        $this->assertEquals(1, $entity->getExpiredAfter());
        $this->assertNotNull($entity->getCreatedAt());
        $this->assertTrue($entity->getIsExpired());
        $this->assertEquals(0, $entity->delete());
    }

    /**
     * @group entity
     * @group timestamp
     * @param int $severalTimes
     * @throws Throwable
     * @throws StaleObjectException
     * @dataProvider severalTimes
     */
    public function testRemoveIfExpired(int $severalTimes)
    {
        $this->entity = new ExpiredEntity();
        $this->entity->setExpiredAfter(1);
        $this->assertTrue($this->entity->save());
        sleep(2);
        $entity = ExpiredCallbackEntity::findOne($this->entity->getGUID());
        $this->assertTrue(is_array($entity->expiredRemovingCallback) && is_callable($entity->expiredRemovingCallback));
        $this->assertEquals(0, $entity->delete());
        
    }
    
    /**
     * @group entity
     * @group timestamp
     */
    public function testEnabledFields()
    {
       $this->assertNotEmpty($this->entity->enabledTimestampFields());
       $this->entity = new Entity(['expiredAfterAttribute' => 'expired_after']);
       $this->assertNotEmpty($this->entity->enabledTimestampFields());
    }
    
    /**
     * @group entity
     * @group timestamp
     */
    public function testInitDatetime()
    {
        $this->entity = new Entity(['timeFormat' => Entity::TIME_FORMAT_DATETIME]);
        $this->assertEquals(Entity::INIT_DATETIME, $this->entity->initDatetime());
        
        $this->entity = new Entity(['timeFormat' => Entity::TIME_FORMAT_TIMESTAMP]);
        $this->assertEquals(Entity::INIT_TIMESTAMP, $this->entity->initDatetime());
        
        $this->entity = new Entity(['timeFormat' => -1]);
        $this->assertNull($this->entity->initDatetime());
    }

    /**
     * @group entity
     * @group timestamp
     */
    public function testCurrentDatetime()
    {
        $this->entity = new Entity(['timeFormat' => Entity::TIME_FORMAT_DATETIME]);
        $this->assertEquals(date('Y-m-d H:i:s'), $this->entity->currentDatetime());
        
        $this->entity = new Entity(['timeFormat' => Entity::TIME_FORMAT_TIMESTAMP]);
        $this->assertEquals(time(), $this->entity->currentDatetime());
        
        $this->entity = new Entity(['timeFormat' => -1]);
        $this->assertNull($this->entity->currentDatetime());
    }

    /**
     * Local timezone is 'Asia/Shanghai', eight hours earlier than GMT.
     * Therefore, Greenwich time and Beijing time is not the same thing.
     * However, the timestamp has nothing to do with the time zone, the uniform use of Greenwich time.
     * @group entity
     * @group timestamp
     */
    public function testCurrentLocalDatetime()
    {
        $this->entity = new EntityLocal(['timeFormat' => Entity::TIME_FORMAT_DATETIME]);
        $this->assertEquals(gmdate('Y-m-d H:i:s'), $this->entity->currentUtcDatetime());
        
        $this->entity = new EntityLocal(['timeFormat' => Entity::TIME_FORMAT_TIMESTAMP]);
        $this->assertEquals(time(), $this->entity->currentUtcDatetime());
        
        $this->entity = new EntityLocal(['timeFormat' => -1]);
        $this->assertNull($this->entity->currentDatetime());
    }

    /**
     * @group entity
     * @group timestamp
     */
    public function testOffsetDatetime()
    {
        $this->entity = new Entity(['timeFormat' => Entity::TIME_FORMAT_DATETIME]);
        $this->assertEquals(date('Y-m-d H:i:s', strtotime("2 seconds")), $this->entity->offsetDatetime(date('Y-m-d H:i:s'), 2));
        
        $this->entity = new Entity(['timeFormat' => Entity::TIME_FORMAT_TIMESTAMP]);
        $this->assertEquals(time() + 2, $this->entity->offsetDatetime(null, 2));
        
        $this->entity = new Entity(['timeFormat' => -1]);
        $this->assertNull($this->entity->offsetDatetime());
    }

    /**
     * Local timezone is 'Asia/Shanghai', eight hours earlier than GMT.
     * Therefore, Greenwich time and Beijing time is not the same thing.
     * However, the timestamp has nothing to do with the time zone, the uniform use of Greenwich time.
     * @group entity
     * @group timestamp
     */
    public function testOffsetDatetimeLocal()
    {
        $this->entity = new EntityLocal(['timeFormat' => EntityLocal::TIME_FORMAT_DATETIME]);
        $this->assertEquals(gmdate('Y-m-d H:i:s', strtotime("2 seconds")), $this->entity->offsetDatetime(gmdate('Y-m-d H:i:s'), 2));
        
        $this->entity = new EntityLocal(['timeFormat' => EntityLocal::TIME_FORMAT_TIMESTAMP]);
        $this->assertEquals(time() + 2, $this->entity->offsetDatetime(null, 2));
        
        $this->entity = new EntityLocal(['timeFormat' => -1]);
        $this->assertNull($this->entity->offsetDatetime());
    }

    /**
     * @group entity
     * @group timestamp
     */
    public function testCreatedAtRules()
    {
        $this->entity = new Entity(['createdAtAttribute' => false]);
        $this->assertNull($this->entity->getCreatedAt());
        $this->assertEmpty($this->entity->getCreatedAtRules());
        $this->assertTrue($this->entity->save());
    }

    /**
     * @group entity
     * @group timestamp
     * @throws Exception
     */
    public function testUpdatedAtChanged()
    {
        $this->entity->content = \Yii::$app->security->generateRandomString();
        $updatedAt = $this->entity->getUpdatedAt();
        sleep(1);
        $this->assertTrue($this->entity->save());
        $this->assertNotEquals($updatedAt, $this->entity->getUpdatedAt());
    }
    
    /**
     * @group entity
     * @group timestamp
     */
    public function testUpdatedAtRules()
    {
        $this->entity = new Entity(['updatedAtAttribute' => false]);
        $this->assertNull($this->entity->getUpdatedAt());
        $this->assertEmpty($this->entity->getUpdatedAtRules());
        $this->assertTrue($this->entity->save());
    }

    /**
     * @group entity
     * @group timestamp
     */
    public function testRange()
    {
        $this->entity = new EntityLocal();
        $this->assertTrue($this->entity->save());
        $this->assertInstanceOf(Entity::class, Entity::find()->createdAt($this->entity->offsetDatetime($this->entity->currentDatetime(), -1))->one());
        $this->assertInstanceOf(Entity::class, Entity::find()->createdAt(null, $this->entity->offsetDatetime($this->entity->currentDatetime(), 1))->one());
        $this->assertInstanceOf(Entity::class, Entity::find()->createdAt($this->entity->offsetDatetime($this->entity->currentDatetime(), -1), $this->entity->offsetDatetime($this->entity->currentDatetime(), 1))->one());
        
        $this->assertNull(Entity::find()->createdAt($this->entity->offsetDatetime($this->entity->currentDatetime(), +1))->one());
        $this->assertNull(Entity::find()->createdAt(null, $this->entity->offsetDatetime($this->entity->currentDatetime(), -1))->one());
        $this->assertNull(Entity::find()->createdAt($this->entity->offsetDatetime($this->entity->currentDatetime(), 1), $this->entity->offsetDatetime($this->entity->currentDatetime(), -1))->one());
        $this->assertGreaterThanOrEqual(1, $this->entity->delete());
    }

    /**
     * @group entity
     * @group timestamp
     */
    public function testRangeUtc()
    {
        $this->assertTrue($this->entity->save());
        $this->assertInstanceOf(Entity::class, Entity::find()->createdAt($this->entity->offsetDatetime($this->entity->currentUtcDatetime(), -1))->one());
        $this->assertInstanceOf(Entity::class, Entity::find()->createdAt(null, $this->entity->offsetDatetime($this->entity->currentUtcDatetime(), 1))->one());
        $this->assertInstanceOf(Entity::class, Entity::find()->createdAt($this->entity->offsetDatetime($this->entity->currentUtcDatetime(), -1), $this->entity->offsetDatetime($this->entity->currentUtcDatetime(), 1))->one());
        
        $this->assertNull(Entity::find()->createdAt($this->entity->offsetDatetime($this->entity->currentUtcDatetime(), +1))->one());
        $this->assertNull(Entity::find()->createdAt(null, $this->entity->offsetDatetime($this->entity->currentUtcDatetime(), -1))->one());
        $this->assertNull(Entity::find()->createdAt($this->entity->offsetDatetime($this->entity->currentUtcDatetime(), 1), $this->entity->offsetDatetime($this->entity->currentUtcDatetime(), -1))->one());
        $this->assertGreaterThanOrEqual(1, $this->entity->delete());
    }
    
    /**
     * @group entity
     * @group timestamp
     */
    public function testLocalDatetime()
    {
        $this->entity = new EntityLocal();
        $this->assertTrue($this->entity->save());
    }
    
    public static function severalTimes(): \Generator
    {
        for ($i = 0; $i < 3; $i++)
        {
            yield [$i];
        }
    }
}