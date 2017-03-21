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

/**
 * @author vistart <i@vistart.me>
 */
class ValidTest extends UserTestCase
{
    /**
     * @group user
     * @group user-valid
     */
    public function testValid()
    {
        $this->assertTrue($this->user->register());
        
        $this->assertInstanceOf($this->user->className(), $this->user->isValid($this->user));
        $this->assertInstanceOf($this->user->className(), $this->user->isValid(null));
        
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group user
     * @group user-valid
     */
    public function testInvalid()
    {
        \Yii::$app->user->setIdentity(null);
        $this->assertFalse($this->user->isValid(null));
        $this->assertFalse($this->user->isValid(new static));
    }
}