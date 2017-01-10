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

use rhosocial\base\helpers\Number;
use rhosocial\base\models\tests\data\ar\User;

class IdentityTest extends UserTestCase
{
    /**
     * @group user
     * @group identity
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testFindIdentity($severalTimes)
    {
        $this->assertTrue($this->user->register());
        $user = User::findIdentity($this->user->getID());
        $this->assertInstanceOf(User::class, $user);
        $user = User::findIdentity($this->user->getID() . '1');
        $this->assertNull($user);
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group user
     * @group identity
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testFindIdentityByGUID($severalTimes)
    {
        $this->assertTrue($this->user->register());
        $user = User::findIdentityByGuid($this->user);
        $this->assertInstanceOf(User::class, $user);
        $user = User::findIdentityByGuid(Number::guid_bin());
        $this->assertNull($user);
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group user
     * @group identity
     */
    public function testStatus()
    {
        $this->assertTrue($this->user->register());
        $this->assertEquals(User::$statusActive, $this->user->status);
        $this->assertTrue($this->user->deregister());
        
        $this->user->status = User::$statusInactive;
        $this->assertTrue($this->user->register());
        $this->assertEquals(User::$statusInactive, $this->user->status);
        $this->user->setStatus(User::$statusActive);
        $this->assertTrue($this->user->save());
        $this->assertEquals(User::$statusActive, $this->user->getStatus());
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group user
     * @group identity
     */
    public function testStatusRules()
    {
        $rules = [
            [[$this->user->statusAttribute], 'required'],
            [[$this->user->statusAttribute], 'integer', 'min' => 0],
        ];
        $this->user->setStatusRules($rules);
        $this->assertTrue($this->user->validate());
    }
    
    /**
     * @group user
     * @group identity
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testAccessToken($severalTimes)
    {
        $this->assertNotEmpty($this->user->accessToken);
        $this->assertTrue($this->user->register());
        $this->assertNotEmpty($this->user->getAccessToken());
        $this->assertInstanceOf(User::class, User::findIdentityByAccessToken($this->user->accessToken));
        $this->assertEquals(1, User::find()->guid($this->user)->andWhere([$this->user->accessTokenAttribute => $this->user->accessToken])->count());
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group user
     * @group identity
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testSetAccessToken($severalTimes)
    {
        $this->assertTrue($this->user->register());
        $accessToken = sha1(\Yii::$app->security->generateRandomString());
        $this->user->accessToken = $accessToken;
        $this->user->setAccessToken(sha1(\Yii::$app->security->generateRandomString()));
        $this->assertNotEquals($accessToken, $this->user->getAccessToken());
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group user
     * @group identity
     */
    public function testAccessTokenRulesPass()
    {
        $this->assertNotEmpty($this->user->getAccessTokenRules());
        $this->user->setAccessToken(sha1(\Yii::$app->security->generateRandomString()));
        $this->user->setAccessTokenRules([
            [[$this->user->accessTokenAttribute], 'required'],
            [[$this->user->accessTokenAttribute], 'string', 'max' => strlen($this->user->accessToken) + 1],
        ]);
        $this->assertTrue($this->user->validate());
    }
        
    /**
     * @group user
     * @group identity
     */
    public function testAccessTokenRulesNotPass()
    {
        $this->user->accessToken = sha1(\Yii::$app->security->generateRandomString());
        $this->user->accessTokenRules = [
            [[$this->user->accessTokenAttribute], 'required'],
            [[$this->user->accessTokenAttribute], 'string', 'max' => mb_strlen($this->user->accessToken, 'utf8') - 1],
        ];
        $this->assertFalse($this->user->validate());
    }
    
    public function severalTimes()
    {
        for ($i = 0; $i < 3; $i++) {
            yield [$i];
        }
    }
}