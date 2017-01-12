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

namespace rhosocial\base\models\tests\user;

use rhosocial\base\models\tests\data\ar\User;
use rhosocial\base\models\tests\TestCase;

class UserTestCase extends TestCase
{
    /**
     *
     * @var User
     */
    protected $user = null;
    
    protected function setUp()
    {
        parent::setUp();
        $this->user = new User();
    }
    
    protected function tearDown()
    {
        User::deleteAll();
        parent::tearDown();
    }
}