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

namespace rhosocial\base\models\tests;

use rhosocial\base\models\tests\data\ar\RedisEntity;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class RedisEntityTest extends TestCase
{
    /**
     * @group redis
     * @group entity
     */
    public function testNew ()
    {
        $entity = new RedisEntity();
        $this->assertTrue($entity->save());
        $query = RedisEntity::find()->id($entity->id);
        $query1 = clone $query;
        $this->assertInstanceOf(RedisEntity::class, $query1->one());
        $this->assertEquals(1, $entity->delete());
    }
}