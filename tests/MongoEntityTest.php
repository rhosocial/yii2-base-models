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

namespace rhosocial\base\models\tests;

use rhosocial\base\helpers\Number;
use rhosocial\base\models\tests\data\ar\MongoEntity;

/**
 * @author vistart <i@vistart.me>
 */
class MongoEntityTest extends MongoTestCase
{
    /**
     * @group entity
     * @group mongo
     */
    public function testNew()
    {
        $entity = new MongoEntity();
        if ($entity->save()) {
            $this->assertTrue(true);
        } else {
            var_dump($entity->errors);
        }
        $query = MongoEntity::find()->guid($entity->guid);
        $query1 = clone $query;
        $this->assertInstanceOf(MongoEntity::class, $query1->one());
        $this->assertEquals(1, $entity->delete());
    }
    
    /**
     * @group entity
     * @group mongo
     */
    public function testGUID()
    {
        $entity = new MongoEntity();
        $this->assertTrue($entity->save());
        $existed = MongoEntity::find()->guid($entity->guid)->one();
        $this->assertRegExp(Number::GUID_REGEX, $existed->getGUID());
        $this->assertEquals(1, $existed->delete());
    }
    
    /**
     * @group entity
     * @group mongo
     */
    public function testIPv4()
    {
        $ipAddress = "192.168.1.1";
        $entity = new MongoEntity();
        $entity->setIPAddress($ipAddress);
        $this->assertTrue($entity->save());
        $existed = MongoEntity::find()->guid($entity->guid)->one();
        /* @var MongoEntity $existed */
        $this->assertEquals($ipAddress, $existed->getIPAddress());
        $this->assertEquals(1, $existed->delete());
    }
    
    /**
     * @group entity
     * @group mongo
     */
    public function testIPv6()
    {
        $ipAddress = "fe80::34da:3d02:2210:3ab9";
        $entity = new MongoEntity();
        $entity->setIPAddress($ipAddress);
        $this->assertTrue($entity->save());
        $existed = MongoEntity::find()->guid($entity->guid)->one();
        /* @var MongoEntity $existed */
        $this->assertEquals($ipAddress, $existed->getIPAddress());
        $this->assertEquals(1, $existed->delete());
    }
}