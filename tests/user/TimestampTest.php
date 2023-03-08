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
use rhosocial\base\models\tests\data\ar\ExpiredUser;
use rhosocial\base\models\tests\user\UserTestCase;
use Throwable;
use yii\db\IntegrityException;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class TimestampTest extends UserTestCase
{
    protected function setUp() : void {
        parent::setUp();
        $this->user = new ExpiredUser();
    }

    /**
     * @group user
     * @group registration
     * @group timestamp
     * @param int $severalTimes
     * @throws IntegrityException
     * @throws Throwable
     * @dataProvider timestampProvider
     */
    public function testAfterRegister(int $severalTimes)
    {
        $this->assertNull($this->user->getCreatedAt());
        $this->assertNull($this->user->getUpdatedAt());
        $this->assertTrue($this->user->register());
        $this->assertNotNull($this->user->getCreatedAt());
        $this->assertNotNull($this->user->getUpdatedAt());
        $this->assertEquals($this->user->getCreatedAt(), $this->user->getUpdatedAt());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group timestamp
     * @param int $severalTimes
     * @throws IntegrityException
     * @throws Throwable
     * @dataProvider timestampProvider
     */
    public function testNoCreatedAt(int $severalTimes)
    {
        $this->user = new ExpiredUser(['createdAtAttribute' => false]);
        $this->assertNull($this->user->getCreatedAt());
        $this->assertNull($this->user->getUpdatedAt());
        $this->assertTrue($this->user->register());
        $this->assertNull($this->user->getCreatedAt());
        $this->assertNotNull($this->user->getUpdatedAt());
        $this->assertNotEquals($this->user->getCreatedAt(), $this->user->getUpdatedAt());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group timestamp
     * @param int $severalTimes
     * @throws IntegrityException
     * @throws Throwable
     * @dataProvider timestampProvider
     */
    public function testNoUpdatedAt(int $severalTimes)
    {
        $this->user = new ExpiredUser(['updatedAtAttribute' => false]);
        $this->assertNull($this->user->getCreatedAt());
        $this->assertNull($this->user->getUpdatedAt());
        $this->assertTrue($this->user->register());
        $this->assertNotNull($this->user->getCreatedAt());
        $this->assertNull($this->user->getUpdatedAt());
        $this->assertNotEquals($this->user->getCreatedAt(), $this->user->getUpdatedAt());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group timestamp
     * @param int $severalTimes
     * @throws IntegrityException
     * @throws Throwable
     * @dataProvider timestampProvider
     */
    public function testNoTimestamp(int $severalTimes)
    {
        $this->user = new ExpiredUser(['createdAtAttribute' => false, 'updatedAtAttribute' => false]);
        $this->assertNull($this->user->getCreatedAt());
        $this->assertNull($this->user->getUpdatedAt());
        $this->assertTrue($this->user->register());
        $this->assertNull($this->user->getCreatedAt());
        $this->assertNull($this->user->getUpdatedAt());
        $this->assertEquals($this->user->getCreatedAt(), $this->user->getUpdatedAt());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group timestamp
     * @param int $severalTimes
     * @throws Throwable
     * @throws IntegrityException
     * @dataProvider timestampProvider
     */
    public function testRemoveIfExpired(int $severalTimes)
    {
        $this->assertNull($this->user->getCreatedAt());
        $this->assertNull($this->user->getUpdatedAt());
        $this->assertFalse($this->user->getIsExpired());
        $this->assertTrue($this->user->register());
        $this->assertNotNull($this->user->getCreatedAt());
        $this->assertNotNull($this->user->getUpdatedAt());
        $this->assertEquals($this->user->getCreatedAt(), $this->user->getUpdatedAt());
        $this->user->setExpiredAfter(1);
        sleep(2);
        $this->assertFalse($this->user->getIsNewRecord());
        $this->assertTrue($this->user->getIsExpired());
        $this->assertTrue($this->user->refresh());
        $this->assertTrue($this->user->getIsNewRecord());
        $this->assertFalse($this->user->deregister());
    }

    /**
     * @group user
     * @group timestamp
     */
    public function testEnabledFields()
    {
        $this->assertNotEmpty($this->user->enabledTimestampFields());
    }

    /**
     * @group user
     * @group timestamp
     */
    public function testNoExpiration()
    {
        $this->user = new User(['password' => '123456']);
        $this->assertFalse($this->user->setExpiredAfter(1));
    }

    /**
     * @group user
     * @group timestamp
     */
    public function testHasEverBeenEdited()
    {
        $this->assertTrue($this->user->register());
        $this->assertNotEmpty($this->user->createdAtAttribute);
        $this->assertNotEmpty($this->user->updatedAtAttribute);
        $this->assertEquals($this->user->getCreatedAt(), $this->user->getUpdatedAt());
        $this->assertNotEmpty($this->user->getCreatedAt());
        $this->assertFalse($this->user->hasEverBeenEdited());
        sleep(2);
        $this->user->password = \Yii::$app->security->generateRandomString();
        $this->assertTrue($this->user->save());
        $this->assertNotEquals($this->user->getCreatedAt(), $this->user->getUpdatedAt());
        $this->assertTrue($this->user->hasEverBeenEdited());

        $this->assertEquals(1, $this->user->setExpiredAfter(1));
        $this->assertTrue($this->user->save());
        sleep(2);
        ($this->user->refresh());
        $this->assertTrue($this->user->getIsNewRecord());
        $this->assertNull(User::find()->guid($this->user->getGUID())->one());
    }


    public static function timestampProvider(): \Generator
    {
        for ($i = 0; $i < 3; $i++)
        {
            yield [$i];
        }
    }
}