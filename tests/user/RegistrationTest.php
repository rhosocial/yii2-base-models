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

namespace rhosocial\base\models\tests\user;

use rhosocial\base\models\tests\data\ar\User;

/**
 * @author vistart <i@vistart.me>
 */
class RegistrationTest extends UserTestCase
{
    /**
     * @group user
     * @group registration
     */
    public function testRegisterSucceed()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group user
     * @group registration
     */
    public function testRegisterFail()
    {
        $this->user = new User();
        $this->user->setPasswordResetToken(sha1(\Yii::$app->security->generateRandomString()) . '1');
        $result = $this->user->register();
        $this->assertInstanceOf(\Exception::class, $result);
        $this->assertFalse($this->user->deregister());
    }
    
    /**
     * @group user
     * @group source
     */
    public function testSetSource()
    {
        $source = $this->user->setSource(\Yii::$app->security->generateRandomString());
        $this->assertEquals($source, $this->user->getSource());
    }
    
    /**
     * @group user
     * @group source
     */
    public function testSourceRules()
    {
        $rules = [
            [[$this->user->sourceAttribute], 'required'],
            [[$this->user->sourceAttribute], 'string', 'max' => 10],
        ];
        $this->user->setSourceRules($rules);
        $this->user->setSource(\Yii::$app->security->generateRandomString(11));
        $this->assertFalse($this->user->validate());
    }
}