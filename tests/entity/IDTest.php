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

/**
 * @author vistart <i@vistart.me>
 */
class IDTest extends EntityTestCase
{
    /**
     * @group entity
     * @group id
     */
    public function testNew()
    {
        $this->assertNotEmpty($this->entity->getID());
        $this->assertTrue($this->entity->save());
        $id = $this->entity->getID();
        $this->entity->setID($this->entity->generateId());
        $this->assertTrue($this->entity->save());
        $this->assertGreaterThanOrEqual(1, $this->entity->delete());
    }
    
    /**
     * @group entity
     * @group id
     */
    public function testExists()
    {
        $this->assertTrue($this->entity->save());
        $id = $this->entity->getID();
        $this->assertTrue($this->entity->checkIdExists($id));
        $this->entity->setID($this->entity->generateId());
        $this->assertTrue($this->entity->save());
        $this->assertFalse($this->entity->checkIdExists($id));
        $this->assertFalse($this->entity->checkIdExists(null));
        $this->assertGreaterThanOrEqual(1, $this->entity->delete());
    }
}