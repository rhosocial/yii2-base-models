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

namespace rhosocial\base\models\tests\user;

use rhosocial\base\models\tests\data\ar\User;
use rhosocial\base\models\tests\TestCase;


/**
 * @author vistart <i@vistart.me>
 */
class UserTestCase extends TestCase
{
    /**
     *
     * @var User
     */
    protected $user = null;
    
    protected function setUp() : void {
        parent::setUp();
        $this->user = new User();
        \Yii::$app->user->identity = $this->user;
    }
    
    protected function tearDown() : void {
        if ($this->user instanceof User) {
            try {
                $this->user->deregister();
            } catch (\Exception $ex) {

            } finally {
                $this->user = null;
            }
        }
        User::deleteAll();
        parent::tearDown();
    }
}