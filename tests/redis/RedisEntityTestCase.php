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

namespace rhosocial\base\models\tests\redis;

use rhosocial\base\models\tests\entity\EntityTestCase;
use rhosocial\base\models\tests\data\ar\RedisEntity;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class RedisEntityTestCase extends EntityTestCase
{
    /**
     * @var RedisEntity;
     */
    protected $entity = null;

    protected function setUp() : void {
        parent::setUp();
        $this->entity = new RedisEntity();
    }

    protected function tearDown() : void {
        RedisEntity::deleteAll();
        parent::tearDown();
    }
}
