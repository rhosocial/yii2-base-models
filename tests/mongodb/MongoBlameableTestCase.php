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

use rhosocial\base\models\tests\data\ar\User;
use rhosocial\base\models\tests\data\ar\MongoBlameable;
use rhosocial\base\models\tests\MongoTestCase;

/**
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
    
    protected function setUp()
    {
        parent::setUp();
        $this->user = new User(['password' => '123456']);
        $this->other = new User(['password' => '123456']);
        $this->blameable = $this->user->create(MongoBlameable::class, ['content' => \Yii::$app->security->generateRandomString()]);
    }
    
    protected function tearDown()
    {
        MongoBlameable::deleteAll();
        User::deleteAll();
        parent::tearDown();
    }
}