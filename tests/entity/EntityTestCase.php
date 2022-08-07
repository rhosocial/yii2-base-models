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

use rhosocial\base\models\tests\TestCase;
use rhosocial\base\models\tests\data\ar\Entity;
use rhosocial\base\models\tests\data\ar\EntityAI;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class EntityTestCase extends TestCase
{
    /**
     *
     * @var Entity 
     */
    protected $entity = null;

    protected function setUp() : void {
        parent::setUp();
        $this->entity = new Entity();
    }

    protected function tearDown() : void {
        Entity::deleteAll();
        EntityAI::deleteAll();
        parent::tearDown();
    }
}

