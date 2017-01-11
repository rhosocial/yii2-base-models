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

use rhosocial\base\models\tests\TestCase;
use rhosocial\base\models\tests\data\ar\Entity;
use rhosocial\base\models\tests\data\ar\ExpiredEntity;

class EntityTestCase extends TestCase {

    /**
     *
     * @var Entity 
     */
    protected $entity = null;

    protected function setUp() {
        parent::setUp();
        $this->entity = new Entity();
    }
    
    protected function tearDown() {
        if ($this->entity instanceof Entity) {
            if ($entity = Entity::findOne($this->entity->getGUID())) {
                $entity->delete();
            }
            if ($entity = ExpiredEntity::findOne($this->entity->getGUID())) {
                $entity->delete();
            }
            $this->entity = null;
        }
        parent::tearDown();
    }
}
