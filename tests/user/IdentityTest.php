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

use rhosocial\base\helpers\Number;
use rhosocial\base\models\tests\data\ar\User;
use Throwable;
use yii\base\Exception;
use yii\db\IntegrityException;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class IdentityTest extends UserTestCase
{
    /**
     * @group user
     * @group identity
     * @param int $severalTimes
     * @dataProvider severalTimes
     * @throws IntegrityException|Throwable
     */
    public function testFindIdentity(int $severalTimes)
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
     * @param int $severalTimes
     * @dataProvider severalTimes
     * @throws IntegrityException|Throwable
     */
    public function testFindIdentityByGUID(int $severalTimes)
    {
        $this->assertTrue($this->user->register());
        $user = User::findIdentityByGuid($this->user);
        $this->assertInstanceOf(User::class, $user);
        $user = User::findIdentityByGuid(Number::guid());
        $this->assertNull($user);
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group identity
     * @throws IntegrityException|Throwable
     */
    public function testStatus()
    {
        $this->assertTrue($this->user->register());
        $this->assertEquals(User::STATUS_ACTIVE, $this->user->status);
        $this->assertTrue($this->user->deregister());
        
        $this->user->status = User::STATUS_INACTIVE;
        $this->assertTrue($this->user->register());
        $this->assertEquals(User::STATUS_INACTIVE, $this->user->status);
        $this->user->setStatus(User::STATUS_ACTIVE);
        $this->assertTrue($this->user->save());
        $this->assertEquals(User::STATUS_ACTIVE, $this->user->getStatus());
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
     * @param int $severalTimes
     * @dataProvider severalTimes
     * @throws IntegrityException|Throwable
     */
    public function testAccessToken(int $severalTimes)
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
     * @param int $severalTimes
     * @dataProvider severalTimes
     * @throws IntegrityException|Exception|Throwable
     */
    public function testSetAccessToken(int $severalTimes)
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
     * @throws Exception
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
     * @throws Exception
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

    /**
     * @group user
     * @group identity
     * @param int $severalTimes
     * @throws Exception
     * @throws IntegrityException|Throwable
     * @dataProvider severalTimes
     */
    public function testAuthKey(int $severalTimes)
    {
        $this->assertNotEmpty($this->user->authKey);
        $this->assertTrue($this->user->register());
        $this->assertNotEmpty($this->user->getAuthKey());
        $authKey = $this->user->getAuthKey();
        $this->user->authKey = sha1(\Yii::$app->security->generateRandomString());
        $this->assertFalse($this->user->validateAuthKey($authKey));
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group identity
     * @throws Exception
     */
    public function testAuthKeyRulesPass()
    {
        $this->user->authKey = sha1(\Yii::$app->security->generateRandomString());
        $this->user->authKeyRules = [
            [[$this->user->authKeyAttribute], 'required'],
            [[$this->user->authKeyAttribute], 'string', 'max' => mb_strlen($this->user->authKey, 'utf8') + 1],
        ];
        $this->assertTrue($this->user->validate());
    }

    /**
     * @group user
     * @group identity
     * @throws Exception
     */
    public function testAuthKeyRulesNotPass()
    {
        $this->user->authKey = sha1(\Yii::$app->security->generateRandomString());
        $this->user->authKeyRules = [
            [[$this->user->authKeyAttribute], 'required'],
            [[$this->user->authKeyAttribute], 'string', 'max' => mb_strlen($this->user->authKey, 'utf8') - 1],
        ];
        $this->assertFalse($this->user->validate());
    }
    
    public static function severalTimes(): \Generator
    {
        for ($i = 0; $i < 3; $i++) {
            yield [$i];
        }
    }
}