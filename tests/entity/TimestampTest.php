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

namespace rhosocial\base\models\tests\entity;

use rhosocial\base\models\tests\data\ar\Entity;
use rhosocial\base\models\tests\data\ar\ExpiredEntity;
/**
 * @author vistart <i@vistart.me>
 */
class TimestampTest extends EntityTestCase
{
    /**
     * @group entity
     * @group timestamp
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testNotExpired($severalTimes)
    {
        $this->assertFalse($this->entity->expiredAfterAttribute);
        $this->assertFalse($this->entity->getIsExpired());
        $this->assertFalse($this->entity->setExpiredAfter(1));
    }
    
    /**
     * @group entity
     * @group timestamp
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testExpired($severalTimes)
    {
        $this->entity = new ExpiredEntity();
        $this->entity->setExpiredAfter(1);
        $this->assertEquals(1, $this->entity->getExpiredAfter());
        $this->assertTrue($this->entity->save());
        $this->assertNotNull($this->entity->getCreatedAt());
        $this->assertNotNull($this->entity->getUpdatedAt());
        sleep(2);
        $entity = ExpiredEntity::findOne($this->entity->getGUID());
        $this->assertEquals(1, $entity->getExpiredAfter());
        $this->assertNotNull($entity->getCreatedAt());
        $this->assertTrue($entity->getIsExpired());
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
        $this->entity = new Entity(['timeFormat' => Entity::$timeFormatDatetime]);
        $this->assertEquals(Entity::$initDatetime, $this->entity->initDatetime());
        
        $this->entity = new Entity(['timeFormat' => Entity::$timeFormatTimestamp]);
        $this->assertEquals(Entity::$initTimestamp, $this->entity->initDatetime());
        
        $this->entity = new Entity(['timeFormat' => -1]);
        $this->assertNull($this->entity->initDatetime());
    }
    
    /**
     * @group entity
     * @group timestamp
     */
    public function testCreatedAtRules()
    {
        $this->entity = new Entity(['createdAtAttribute' => false]);
        $this->assertEmpty($this->entity->getCreatedAtRules());
        $this->assertTrue($this->entity->save());
    }
    
    /**
     * @group entity
     * @group timestamp
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
        $this->assertEmpty($this->entity->getUpdatedAtRules());
        $this->assertTrue($this->entity->save());
    }
    
    public function severalTimes()
    {
        for ($i = 0; $i < 3; $i++)
        {
            yield [$i];
        }
    }
}