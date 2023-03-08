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

namespace rhosocial\base\models\tests\user;

use rhosocial\base\models\tests\data\ar\User;
use yii\db\IntegrityException;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class QueryTest extends UserTestCase
{
    /**
     * @group user
     * @group query
     * @throws IntegrityException
     */
    public function testActive()
    {
        $this->assertTrue($this->user->register());
        $this->assertEquals(User::STATUS_ACTIVE, $this->user->status);
        $this->assertEquals(User::STATUS_ACTIVE, $this->user->getStatus());
        $this->assertInstanceOf(User::class, User::find()->guid($this->user->getGUID())->active(User::STATUS_ACTIVE)->one());
        $this->assertNull(User::find()->guid($this->user->getGUID())->active(User::STATUS_INACTIVE)->one());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group query
     * @throws IntegrityException
     */
    public function testInactive()
    {
        $this->user->status = User::STATUS_INACTIVE;
        $this->assertTrue($this->user->register());
        $this->assertEquals(User::STATUS_INACTIVE, $this->user->status);
        $this->assertEquals(User::STATUS_INACTIVE, $this->user->getStatus());
        $this->assertNull(User::find()->guid($this->user->getGUID())->active(User::STATUS_ACTIVE)->one());
        $this->assertInstanceOf(User::class, User::find()->guid($this->user->getGUID())->active(User::STATUS_INACTIVE)->one());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group query
     * @throws IntegrityException
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