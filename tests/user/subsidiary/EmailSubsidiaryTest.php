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

namespace rhosocial\base\models\tests\user\subsidiary;

use rhosocial\base\models\tests\data\ar\blameable\UserEmail;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class EmailSubsidiaryTest extends SubsidiaryTestCase
{
    /**
     * @var UserEmail
     */
    protected $email = null;

    protected function setUp() : void {
        parent::setUp();
        $this->user->addSubsidiaryClass('Email', UserEmail::class);
        $this->email = $this->user->createEmail(['email' => $this->faker->email(), 'type' => 0]);
    }

    protected function tearDown() : void {
        if ($this->email instanceof UserEmail) {
            $this->email->delete();
        }
        $this->email = null;
        UserEmail::deleteAll();
        parent::tearDown();
    }

    /**
     * @group user
     * @group subsidiary
     */
    public function testInvalid()
    {
        try {
            $this->user->addSubsidiaryClass(null, []);
            $this->fail('InvalidConfigException should be thrown.');
        } catch (\yii\base\InvalidConfigException $ex) {
            $this->assertEquals('Subsidiary name not specified.', $ex->getMessage());
        }
        $this->assertNull($this->user->createEmails(['email' => $this->faker->email()]));
        $faker = $this->faker->email();
        $email = $this->user->createSubsidiary(UserEmail::class, ['email' => $faker]);
        $this->assertInstanceOf(UserEmail::class, $email);
    }

    /**
     * @group user
     * @group subsidiary
     * @depends testInvalid
     */
    public function testAddInvalidSubsidiary()
    {
        try {
            $this->user->addSubsidiaryClass(null, []);
            $this->fail('InvalidConfigException should be thrown.');
        } catch (\yii\base\InvalidConfigException $ex) {
            $this->assertEquals('Subsidiary name not specified.', $ex->getMessage());
        }
        try {
            $this->user->addSubsidiaryClass('Email', []);
            $this->fail('InvalidConfigException should be thrown.');
        } catch (\yii\base\InvalidConfigException $ex) {
            $this->assertEquals('Subsidiary class not specified.', $ex->getMessage());
        }
        $this->user->addSubsidiaryClass('Email', ['class' => UserEmail::class]);
        $this->assertEquals(UserEmail::class, $this->user->getSubsidiaryClass('Email'));
    }

    /**
     * @group user
     * @group subsidiary
     * @depends testInvalid
     */
    public function testNew()
    {
        $this->assertInstanceOf(UserEmail::class, $this->email);
    }

    /**
     * @group user
     * @group subsidiary
     */
    public function testAddValidClass()
    {
        try {
            $this->user->addSubsidiaryClass('Email', ['class' => UserEmail::class]);
        } catch (\Exception $ex) {
            $this->fail($ex->getMessage());
        }
        $this->assertArrayHasKey('email', $this->user->subsidiaryMap);
        $this->assertEquals(['class' => UserEmail::class], $this->user->subsidiaryMap['email']);
    }

    /**
     * @group user
     * @group subsidiary
     * @depends testAddValidClass
     */
    public function testCreateValidModel()
    {
        try {
            $this->user->addSubsidiaryClass('Email', ['class' => UserEmail::class]);
        } catch (\Exception $ex) {
            $this->fail($ex->getMessage());
        }
        try {
            $faker = $this->faker->email();
            $model = $this->user->createEmail(['email' => $faker]);
        } catch (\Exception $ex) {
            $this->fail($ex->getMessage());
        }
        $this->assertInstanceOf(UserEmail::class, $model);
    }

    /**
     * @group user
     * @group subsidiary
     * @depends testNew
     */
    public function testSave()
    {
        $email = $this->email->email;
        $this->assertTrue($this->user->register([$this->email]));
        $this->assertEquals($email, $this->email->email);
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group subsidiary
     * @depends testNew
     */
    public function testFindAll()
    {
        $email = $this->email->email;
        $this->assertTrue($this->user->register([$this->email]));
        $emails = $this->user->getSubsidiaries('Email');
        $this->assertCount(1, $emails);
        $this->assertEquals($email, $emails[0]->email);
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group subsidiary
     */
    public function testRemove()
    {
        $email = $this->email->email;
        $this->assertTrue($this->user->register([$this->email]));
        $emails = $this->user->getSubsidiaries('Email');
        $this->assertCount(1, $emails);
        $this->assertFalse($this->user->removeSubsidiary('Emails'));
        $this->assertTrue($this->user->removeSubsidiary('Email'));
        $this->assertFalse($this->user->removeSubsidiary('Email'));
        $this->assertFalse($this->user->removeSubsidiary('Emails'));
        $this->assertNull($this->user->getSubsidiaries('Email'));
        $this->assertTrue($this->user->deregister());
    }
}
