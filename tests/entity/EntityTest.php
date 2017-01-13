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
}