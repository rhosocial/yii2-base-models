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

namespace rhosocial\base\models\tests\mongodb;

use MongoDB\BSON\Binary;
use rhosocial\base\helpers\Number;
use rhosocial\base\models\tests\data\ar\MongoEntity;
use rhosocial\base\models\tests\data\ar\Entity;
use yii\base\Exception;
use yii\db\StaleObjectException;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class MongoEntityTest extends MongoEntityTestCase
{
    /**
     * @group mongo
     * @group entity
     * @param int $severalTimes
     * @throws StaleObjectException
     * @dataProvider severalTimes
     */
    public function testNew(int $severalTimes)
    {
        $this->assertTrue($this->entity->save());
        $this->assertEquals(1, $this->entity->delete());
    }

    /**
     * @group mongo
     * @group entity
     * @param int $severalTimes
     * @throws StaleObjectException
     * @dataProvider severalTimes
     */
    public function testGUID(int $severalTimes)
    {
        $this->assertTrue($this->entity->save());
        $guid = $this->entity->getGUID();
        $this->assertMatchesRegularExpression(Number::GUID_REGEX, Number::guid(false, false, $guid));

        $new_guid = MongoEntity::generateGuid();
        $this->entity->setGUID($new_guid);

        $this->assertNotEquals($guid, $this->entity->getGUID());
        $this->assertEquals($new_guid, $this->entity->getGUID());

        $readable = Number::guid();
        $this->entity->setGUID($readable);
        // $this->assertEquals($readable, Number::guid(false, false, (string)($this->entity)));
        $this->assertEquals($readable, (string)($this->entity));
        $this->assertEquals(1, $this->entity->delete());
    }

    /**
     * @group mongo
     * @group entity
     * @param integer $severalTimes
     * @throws StaleObjectException
     * @dataProvider severalTimes
     */
    public function testCheckGuidExists(int $severalTimes)
    {
        $this->assertTrue($this->entity->save());
        $this->assertTrue(MongoEntity::checkGuidExists($this->entity->getGUID()));
        // $this->assertTrue(MongoEntity::checkGuidExists(Number::guid(false, false, $this->entity->getGUID())));
        $this->assertFalse(MongoEntity::checkGuidExists($this->entity->getGUID() . $this->faker->randomNumber()));
        $this->assertTrue(MongoEntity::checkGuidExists($this->entity->{$this->entity->guidAttribute}));
        // $this->assertFalse(MongoEntity::checkGuidExists(new Binary(Number::guid_bin(), Binary::TYPE_UUID)));
        $this->assertFalse(MongoEntity::checkGuidExists(Number::guid()));
        // $this->assertFalse(MongoEntity::checkGuidExists(null));
        $this->assertEquals(1, $this->entity->delete());
    }

    /**
     * @group mongo
     * @group entity
     * @param int $severalTimes
     * @throws StaleObjectException
     * @dataProvider severalTimes
     */
    public function testIPv4Address(int $severalTimes)
    {
        $ipv4 = $this->faker->ipv4();
        $this->entity->setIPAddress($ipv4);
        $this->assertTrue($this->entity->save());
        $this->assertEquals($ipv4, $this->entity->getIPAddress());
        $this->assertEquals(1, $this->entity->delete());
    }

    /**
     * @group mongo
     * @group entity
     * @param int $severalTimes
     * @throws StaleObjectException
     * @dataProvider severalTimes
     */
    public function testIPv6Address(int $severalTimes)
    {
        $ipv6 = $this->faker->ipv6();
        $this->entity->setIPAddress($ipv6);
        $this->assertTrue($this->entity->save());
        $this->assertEquals($ipv6, $this->entity->getIPAddress());
        $this->assertEquals(1, $this->entity->delete());
    }

    /**
     * @group mongo
     * @group entity
     * @throws Exception
     */
    public function testCompositeGUID()
    {
        $this->assertNull(MongoEntity::compositeGuids(null));

        // $this->assertEquals($this->entity->getGUID(), MongoEntity::compositeGUIDs($this->entity)->getData());
        $this->assertEquals($this->entity->getGUID(), MongoEntity::compositeGUIDs($this->entity));

        $models = [];
        $models[] = $this->entity;
        $models[] = new Entity(['content' => \Yii::$app->security->generateRandomString()]);
        $models[] = Number::guid();

        $guids = MongoEntity::compositeGUIDs($models);
        // $this->assertEquals($guids[0]->getData(), $models[0]->getGUID());
        $this->assertEquals($guids[0], $models[0]->getGUID());
        // $this->assertEquals($guids[1]->getData(), $models[1]->getGUID());
        $this->assertEquals($guids[1], $models[1]->getGUID());
        // $this->assertEquals($guids[2]->getData(), Number::guid_bin($models[2]));
        $this->assertEquals($guids[2], $models[2]);
    }

    public static function severalTimes(): \Generator
    {
        for ($i = 0; $i < 3; $i++)
        {
            yield [$i];
        }
    }
}
