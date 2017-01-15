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

/**
 * @author vistart <i@vistart.me>
 */
class EntityTest extends EntityTestCase
{
    /**
     * @group entity
     */
    public function testAttributes()
    {
        $this->assertInstanceOf(Entity::class, $this->entity);
        $this->assertTrue($this->entity->checkAttributes());
    }
    
    /**
     * @group entity
     */
    public function testInvalidAttributes()
    {
        try {
            $this->entity = new Entity(['idAttribute' => false, 'guidAttribute' => false]);
            $this->fail();
        } catch (\Exception $ex) {
            $this->assertTrue(true);
        }
    }
    
    /**
     * @group entity
     */
    public function testSelfFields()
    {
        $unsetArray = $this->entity->unsetSelfFields();
        if (empty($unsetArray)) {
            $this->fail();
        } else {
            $this->assertArrayHasKey('content', $unsetArray);
            $this->assertArrayHasKey('expired_after', $unsetArray);
        }
    }
    
    /**
     * @group entity
     * @group timestamp
     */
    public function testHasEverEdited()
    {
        $this->assertNotEmpty($this->entity->createdAtAttribute);
        $this->assertNotEmpty($this->entity->updatedAtAttribute);
        $this->assertNotEquals($this->entity->createdAtAttribute, $this->entity->updatedAtAttribute);
        $this->assertTrue($this->entity->save());
        $this->assertFalse($this->entity->hasEverEdited());
        sleep(1);
        $this->assertTrue($this->entity->save());
        $this->assertEquals($this->entity->getCreatedAt(), $this->entity->getUpdatedAt());
        $this->assertFalse($this->entity->hasEverEdited());
        
        $this->entity->content = \Yii::$app->security->generateRandomString();
        sleep(1);
        $this->assertTrue($this->entity->save());
        $this->assertNotEquals($this->entity->getCreatedAt(), $this->entity->getUpdatedAt());
        $this->assertTrue($this->entity->hasEverEdited());
        
        $this->assertGreaterThanOrEqual(1, $this->entity->delete());
    }
}