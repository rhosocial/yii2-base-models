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

/**
 * @author vistart <i@vistart.me>
 */
class InitTest extends UserTestCase
{
    /**
     * @group user
     * @group registration
     * @dataProvider severalTimes
     */
    public function testRegister($i)
    {
        $this->assertTrue($this->user->getIsNewRecord());
        $this->assertFalse($this->user->deregister(), 'False if not registered.');
        $this->assertTrue($this->user->register(), 'True if not registered.');
        $this->assertFalse($this->user->getIsNewRecord());
        $this->assertFalse($this->user->register(), 'False if registered.');
        $this->assertTrue($this->user->deregister(), 'True if registered.');
        $this->assertTrue($this->user->getIsNewRecord());
        $this->assertFalse($this->user->deregister());
    }
    
    public function severalTimes()
    {
        for ($i = 0; $i < 3; $i++) {
            yield [$i];
        }
    }
}