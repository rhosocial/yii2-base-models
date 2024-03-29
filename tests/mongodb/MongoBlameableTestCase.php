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

namespace rhosocial\base\models\tests\mongodb;

use rhosocial\base\models\tests\data\ar\User;
use rhosocial\base\models\tests\data\ar\MongoBlameable;
use rhosocial\base\models\tests\MongoTestCase;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class MongoBlameableTestCase extends MongoTestCase
{
    /**
     * @var User
     */
    protected $user = null;

    /**
     * @var User
     */
    protected $other = null;

    /**
     * @var MongoBlameable;
     */
    protected $blameable = null;

    protected function setUp() : void {
        parent::setUp();
        $this->user = new User(['password' => '123456']);
        $this->other = new User(['password' => '123456']);
        $this->blameable = $this->user->create(MongoBlameable::class, ['content' => \Yii::$app->security->generateRandomString()]);
    }

    protected function tearDown() : void {
        MongoBlameable::deleteAll();
        User::deleteAll();
        parent::tearDown();
    }
}
