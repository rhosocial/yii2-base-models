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

namespace rhosocial\base\models\tests\redis;

use rhosocial\base\models\tests\entity\EntityTestCase;
use rhosocial\base\models\tests\data\ar\RedisEntity;

class RedisEntityTestCase extends EntityTestCase
{
    protected function setUp() {
        parent::setUp();
        $this->entity = new RedisEntity();
    }
    
    protected function tearDown() {
        RedisEntity::deleteAll();
        parent::tearDown();
    }
}