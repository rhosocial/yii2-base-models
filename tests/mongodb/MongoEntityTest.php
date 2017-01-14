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

namespace rhosocial\base\models\tests\mongodb;

use MongoDB\BSON\Binary;
use rhosocial\base\helpers\Number;
use rhosocial\base\models\tests\data\ar\MongoEntity;

/**
 * @author vistart <i@vistart.me>
 */
class MongoEntityTest extends MongoEntityTestCase
{
    /**
     * @group mongo
     * @group entity
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testNew($severalTimes)
    {
        $this->assertTrue($this->entity->save());
        $this->assertEquals(1, $this->entity->delete());
    }
    
    /**
     * @group mongo
     * @group entity
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testGUID($severalTimes)
    {
        $this->assertTrue($this->entity->save());
        $guid = $this->entity->getGUID();
        $this->assertRegExp(Number::GUID_REGEX, Number::guid(false, false, $guid));
        
        $new_guid = MongoEntity::generateGuid();
        $this->entity->setGUID($new_guid);
        
        $this->assertNotEquals($guid, $this->entity->getGUID());
        $this->assertEquals($new_guid, $this->entity->getGUID());
        
        $readable = Number::guid();
        $this->entity->setGUID($readable);
        $this->assertEquals($readable, Number::guid(false, false, (string)($this->entity)));
        $this->assertEquals(1, $this->entity->delete());
    }
    
    /**
     * @group mongo
     * @group entity
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testCheckGuidExists($severalTimes)
    {
        $this->assertTrue($this->entity->save());
        $this->assertTrue(MongoEntity::checkGuidExists($this->entity->getGUID()));
        $this->assertTrue(MongoEntity::checkGuidExists(Number::guid(false, false, $this->entity->getGUID())));
        $this->assertFalse(MongoEntity::checkGuidExists($this->entity->getGUID() . $this->faker->randomNumber()));
        $this->assertTrue(MongoEntity::checkGuidExists($this->entity->{$this->entity->guidAttribute}));
        $this->assertFalse(MongoEntity::checkGuidExists(new Binary(Number::guid(), Binary::TYPE_UUID)));
        $this->assertFalse(MongoEntity::checkGuidExists(null));
        $this->assertEquals(1, $this->entity->delete());
    }
    
    /**
     * @group mongo
     * @group entity
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testIPv4Address($severalTimes)
    {
        $ipv4 = $this->faker->ipv4;
        $this->entity->setIPAddress($ipv4);
        $this->assertTrue($this->entity->save());
        $this->assertEquals($ipv4, $this->entity->getIPAddress());
        $this->assertEquals(1, $this->entity->delete());
    }
    
    /**
     * @group mongo
     * @group entity
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testIPv6Address($severalTimes)
    {
        $ipv6 = $this->faker->ipv6;
        $this->entity->setIPAddress($ipv6);
        $this->assertTrue($this->entity->save());
        $this->assertEquals($ipv6, $this->entity->getIPAddress());
        $this->assertEquals(1, $this->entity->delete());
    }
    
    public function severalTimes()
    {
        for ($i = 0; $i < 3; $i++)
        {
            yield [$i];
        }
    }
}