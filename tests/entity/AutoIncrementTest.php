<?php

/**
 *   _   __ __ _____ _____ ___  ____  _____
 *  | | / // // ___//_  _//   ||  __||_   _|
 *  | |/ // /(__  )  / / / /| || |     | |
 *  |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2022 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\tests\entity;

use rhosocial\base\models\tests\data\ar\EntityAI;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class AutoIncrementTest extends EntityTestCase
{
    protected function setUp() : void {
        parent::setUp();
        $this->entity = new EntityAI();
    }

    /**
     * @group entity
     * @group id
     */
    public function testNew()
    {
        $this->assertTrue($this->entity->save());
    }

    /**
     * @group entity
     * @group id
     */
    public function testGenerateID()
    {
        $this->assertNull($this->entity->generateId());
    }

    /**
     * @group entity
     * @group id
     */
    public function testNotSafe()
    {
        $rules = $this->entity->rules();
        $this->entity = new EntityAI(['idAttributeType' => EntityAI::$idTypeInteger]);
        $this->assertTrue($this->entity->resetCacheKey($this->entity->getEntityRulesCacheKey()));
        $this->assertNotEquals($rules, $this->entity->rules());
    }
}
