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
class IDTest extends UserTestCase
{
    /**
     * @group user
     * @group id
     * @group registration
     * @param int $severalTimes
     * @throws IntegrityException
     * @dataProvider severalTimes
     */
    public function testAfterRegister(int $severalTimes)
    {
        $this->assertFalse($this->user->idPreassigned);
        $id = $this->user->getID();
        $this->assertNotNull($id);
        $this->assertTrue($this->user->register());
        
        $user = User::findOne($this->user->guid);
        $this->assertTrue($user->idPreassigned);
        $this->assertEquals($id, $user->getID());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group id
     * @param int $severalTimes
     * @throws IntegrityException
     * @dataProvider severalTimes
     * @depends      testAfterRegister
     */
    public function testCheckIdExists(int $severalTimes)
    {
        $this->assertFalse($this->user->checkIdExists(null));
        $this->assertFalse($this->user->checkIdExists($severalTimes));
        $this->assertFalse($this->user->checkIdExists($this->user->getID()));
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->user->checkIdExists($this->user->getID()));
        $this->assertTrue($this->user->deregister());
        $this->assertFalse($this->user->checkIdExists($this->user->getID()));
    }

    /**
     * @group user
     * @group id
     * @param int $severalTimes
     * @throws IntegrityException
     * @dataProvider severalTimes
     * @depends      testAfterRegister
     */
    public function testPreassigned(int $severalTimes)
    {
        $this->user = new User(['idPreassigned' => true, 'id' => 123456]);
        $this->assertTrue($this->user->idPreassigned);
        $this->assertEquals(123456, $this->user->getID());
        $this->assertTrue($this->user->register());
        
        $this->assertTrue($this->user->deregister());
    }
    
    public function severalTimes()
    {
        for ($i = 0; $i < 3; $i++) {
            yield [$i];
        }
    }
}