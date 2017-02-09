<?php

/**
 *   _   __ __ _____ _____ ___  ____  _____
 *  | | / // // ___//_  _//   ||  __||_   _|
 *  | |/ // /(__  )  / / / /| || |     | |
 *  |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2017 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\tests\mongodb;

use rhosocial\base\models\tests\data\ar\MongoEntity;
use rhosocial\base\models\tests\MongoTestCase;

class MongoEntityTestCase extends MongoTestCase
{
    /**
     *
     * @var MongoEntity
     */
    protected $entity = null;
    
    protected function setUp() {
        parent::setUp();
        $this->entity = new MongoEntity();
    }
    
    protected function tearDown()
    {
        MongoEntity::deleteAll();
        parent::tearDown();
    }
}