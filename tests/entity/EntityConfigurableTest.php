<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2023 vistart
 * @license https://vistart.me/license/
 */


namespace entity;

use rhosocial\base\models\tests\data\ar\EntityConfigurable;
use rhosocial\base\models\tests\entity\EntityTestCase;

class EntityConfigurableTest extends EntityTestCase
{
    public function testNew() {
        $entity = new EntityConfigurable();
        $this->assertNotNull($entity);
    }
}