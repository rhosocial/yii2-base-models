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

namespace rhosocial\base\models\tests\redis;

use rhosocial\base\models\tests\data\ar\RedisBlameable;
use rhosocial\base\models\tests\user\UserTestCase;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class RedisBlameableTestCase extends UserTestCase
{
    /**
     *
     * @var RedisBlameable
     */
    protected $blameable = null;

    protected function setUp() : void {
        parent::setUp();
        $this->blameable = $this->user->create(RedisBlameable::class, ['class' => RedisBlameable::class, 'content' => \Yii::$app->security->generateRandomString()]);
    }

    protected function tearDown() : void {
        RedisBlameable::deleteAll();
        parent::tearDown();
    }
}
