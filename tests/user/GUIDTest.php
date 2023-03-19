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
use yii\db\IntegrityException;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class GUIDTest extends UserTestCase
{

    /**
     * @group user
     * @group registration
     * @group guid
     * @param int $severalTimes
     * @throws IntegrityException|Throwable
     * @dataProvider severalTimes
     */
    public function testAfterRegister(int $severalTimes)
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue(User::checkGuidExists($this->user->getGUID()));

        $this->assertEquals(36, strlen($this->user->guid));
        $this->assertEquals(36, strlen($this->user->getGUID()));
        $this->assertEquals($this->user->getGUID(), $this->user->guid);

        $this->assertMatchesRegularExpression(Number::GUID_REGEX, $this->user->readableGUID);
        $this->assertMatchesRegularExpression(Number::GUID_REGEX, $this->user->getReadableGUID());
        $this->assertEquals($this->user->getReadableGUID(), $this->user->readableGUID);

        $this->assertTrue($this->user->deregister());
        $this->assertFalse(User::checkGuidExists($this->user->getGUID()));
    }

    /**
     * @group user
     * @group registration
     * @group guid
     * @param int $severalTimes
     * @throws IntegrityException|Throwable
     * @dataProvider severalTimes
     * @depends      testAfterRegister
     */
    public function testFind(int $severalTimes)
    {
        $this->assertTrue($this->user->register());
        $this->assertEquals(36, strlen((string)($this->user)));
        $this->assertInstanceOf(User::class, User::find()->guid($this->user)->one());
        $this->assertInstanceOf(User::class, User::find()->guid($this->user->guid)->one());
        $this->assertEquals(User::find()->guid($this->user->guid)->one()->getGUID(), User::find()->guid($this->user)->one()->getGUID());
        $this->assertInstanceOf(User::class, User::find()->guid($this->user->getGUID())->one());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group guid
     * @param int $severalTimes
     * @throws IntegrityException|Throwable
     * @dataProvider severalTimes
     * @depends      testAfterRegister
     */
    public function testSetBinaryGUID(int $severalTimes)
    {
        $this->assertTrue($this->user->register());
        $oldGUID = $this->user->getGUID();
        $this->user->setGUID($this->user->generateGuid());
        $this->assertEquals(36, strlen($this->user->guid));
        $this->assertNotEquals($oldGUID, $this->user->guid);
        $this->assertTrue($this->user->save());
        $user = User::findOne($this->user->guid);
        $this->assertEquals($this->user->guid, $user->guid);
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group guid
     * @param int $severalTimes
     * @throws IntegrityException|Throwable
     * @dataProvider severalTimes
     * @depends      testAfterRegister
     */
    public function testSetReadableGUID(int $severalTimes)
    {
        $this->assertTrue($this->user->register());
        $oldGUID = $this->user->guid;
        $this->user->setGUID(Number::guid());
        $this->assertEquals(36, strlen($this->user->guid));
        $this->assertNotEquals($oldGUID, $this->user->guid);
        $this->assertTrue($this->user->save());
        $user = User::findOne($this->user->guid);
        $this->assertEquals($this->user->guid, $user->guid);
        $this->assertTrue($this->user->deregister());

        $this->assertMatchesRegularExpression(Number::GUID_REGEX, $this->user->guid = Number::guid());
        $this->assertMatchesRegularExpression(Number::GUID_REGEX, $this->user->{$this->user->getReadableGuidAttribute()});
    }

    public static function severalTimes(): \Generator
    {
        for ($i = 0; $i < 3; $i++) {
            yield [$i];
        }
    }
}