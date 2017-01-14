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

namespace rhosocial\base\models\tests\redis;

use rhosocial\base\models\tests\data\ar\RedisBlameable;
use rhosocial\base\models\tests\user\UserTestCase;

/**
 * @author vistart <i@vistart.me>
 */
class RedisBlameableTestCase extends UserTestCase
{
    /**
     *
     * @var RedisBlameable
     */
    protected $blameable = null;
    
    protected function setUp()
    {
        parent::setUp();
        $this->blameable = $this->user->create(RedisBlameable::class, ['content' => \Yii::$app->security->generateRandomString()]);
    }
    
    protected function tearDown()
    {
        RedisBlameable::deleteAll();
        parent::tearDown();
    }
}