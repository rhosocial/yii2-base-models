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

namespace rhosocial\base\models\tests\user\subsidiary;

use rhosocial\base\models\tests\data\ar\blameable\UserEmail;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class EmailSubsidiaryTest extends SubsidiaryTestCase
{
    /**
     * @var UserEmail
     */
    protected $email = null;
    
    protected function setUp()
    {
        parent::setUp();
        $this->user->addSubsidiary('Email', UserEmail::class);
        $this->email = $this->user->createEmail(['email' => $this->faker->email]);
    }
    
    protected function tearDown()
    {
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
        $this->assertNull($this->user->createSubsidiary(null, []));
        $this->assertNull($this->user->createEmails(['email' => $this->faker->email]));
        $this->assertInstanceOf(UserEmail::class, $this->user->createSubsidiary(UserEmail::class, ['email' => $this->faker->email]));
    }
    
    /**
     * @group user
     * @group subsidiary
     * @depends testInvalid
     */
    public function testAddInvalidSubsidiary()
    {
        try {
            $this->user->addSubsidiary(null, []);
            $this->fail();
        } catch (\Exception $ex) {
            $this->assertEquals('Subsidiary name not specified.', $ex->getMessage());
        }
        try {
            $this->user->addSubsidiary('Email', []);
            $this->fail();
        } catch (\Exception $ex) {
            $this->assertEquals('Subsidiary class not specified.', $ex->getMessage());
        }
        $this->user->addSubsidiary('Email', ['class' => UserEmail::class]);
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
