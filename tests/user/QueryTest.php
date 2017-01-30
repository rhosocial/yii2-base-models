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

class QueryTest extends UserTestCase
{
    /**
     * @group user
     * @group query
     */
    public function testActive()
    {
        $this->assertTrue($this->user->register());
        $this->assertEquals(User::$statusActive, $this->user->status);
        $this->assertEquals(User::$statusActive, $this->user->getStatus());
        $this->assertInstanceOf(User::class, User::find()->guid($this->user->getGUID())->active(User::$statusActive)->one());
        $this->assertNull(User::find()->guid($this->user->getGUID())->active(User::$statusInactive)->one());
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group user
     * @group query
     */
    public function testInactive()
    {
        $this->user->status = User::$statusInactive;
        $this->assertTrue($this->user->register());
        $this->assertEquals(User::$statusInactive, $this->user->status);
        $this->assertEquals(User::$statusInactive, $this->user->getStatus());
        $this->assertNull(User::find()->guid($this->user->getGUID())->active(User::$statusActive)->one());
        $this->assertInstanceOf(User::class, User::find()->guid($this->user->getGUID())->active(User::$statusInactive)->one());
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group user
     * @group query
     */
    public function testSource()
    {
        $this->assertTrue($this->user->register());
        $this->assertEquals('0', $this->user->source);
        
        $users = User::find()->source('0')->all();
        $this->assertTrue($this->user->equals($users[0]));
        
        $this->assertTrue($this->user->deregister());
    }
}