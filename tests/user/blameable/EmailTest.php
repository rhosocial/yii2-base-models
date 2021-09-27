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

namespace rhosocial\base\models\tests\user\blameable;

use rhosocial\base\models\tests\data\ar\blameable\UserEmail;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class EmailTest extends BlameableTestCase
{
    /**
     *
     * @var UserEmail
     */
    public $email = null;

    protected function setUp() : void {
        parent::setUp();
        $this->email = $this->user->create(UserEmail::class, ['email' => $this->faker->email]);
    }

    protected function tearDown() : void {
        UserEmail::deleteAll();
        parent::tearDown();
    }

    /**
     * @group blameable
     * @group email
     */
    public function testNew()
    {
        $this->assertTrue($this->user->register([$this->email]));
        $this->assertTrue($this->user->deregister());
        $this->assertCount(0, UserEmail::findAll([$this->email->createdByAttribute => $this->user->getGUID()]));
    }

    /**
     * @group blameable
     * @group email
     */
    public function testBlameable()
    {
        $this->assertTrue($this->user->register([$this->email]));
        $this->assertCount(1, $this->user->emails);
        unset($this->user->emails);
        $this->assertInstanceOf(UserEmail::class, $this->user->emails[0]);
        unset($this->user->emails);
        $this->assertTrue($this->user->deregister());
        $this->assertCount(0, $this->user->emails);
    }

    /**
     * @group blameable
     * @group email
     */
    public function testEnabledFields()
    {
        $this->assertTrue($this->user->register([$this->email]));
        $this->assertNotEmpty($this->email->enabledFields());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group blameable
     * @group email
     */
    public function testNotConfirmed()
    {
        $this->assertFalse($this->email->getIsConfirmed());
        $this->assertTrue($this->user->register([$this->email]));
        $this->assertFalse($this->email->getIsConfirmed());
        $this->assertTrue($this->user->deregister());
        $this->assertFalse($this->email->getIsConfirmed());
    }

    /**
     * @group blameable
     * @group email
     */
    public function testConfirm()
    {
        $this->assertFalse($this->email->getIsConfirmed());
        $this->assertTrue($this->user->register([$this->email]));
        $this->assertTrue($this->email->applyConfirmation());
        $this->assertTrue($this->email->confirm($this->email->getConfirmCode()));
        $this->assertTrue($this->email->getIsConfirmed());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group blameable
     * @group email
     */
    public function testConfirmation()
    {
        $this->assertFalse($this->email->getIsConfirmed());
        $this->assertTrue($this->user->register([$this->email]));
        $this->assertEquals(UserEmail::$confirmFalse, $this->email->getConfirmation());
        
        $this->assertTrue($this->email->applyConfirmation());
        $this->assertTrue($this->email->confirm($this->email->getConfirmCode()));
        $this->assertTrue($this->email->getIsConfirmed());
        $this->assertEquals(UserEmail::$confirmTrue, $this->email->getConfirmation());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group blameable
     * @group email
     * @depends testConfirm
     */
    public function testResetConfirmation()
    {
        $this->assertTrue($this->user->register([$this->email]));
        $this->assertTrue($this->email->applyConfirmation());
        $this->assertTrue($this->email->confirm($this->email->getConfirmCode()));
        $this->assertTrue($this->email->getIsConfirmed());
        
        $this->email->setContent($this->faker->email);
        $this->assertTrue($this->email->isAttributeChanged($this->email->contentAttribute));
        $this->assertTrue($this->email->save());
        $this->assertFalse($this->email->getIsConfirmed());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group blameable
     * @group email
     */
    public function testQueryConfirmed()
    {
        $this->assertTrue($this->user->register([$this->email]));
        $this->assertFalse($this->email->getIsConfirmed());
        $this->assertNull(UserEmail::find()->guid($this->email->getGUID())->confirmed()->one());
        $this->assertInstanceOf(UserEmail::class, UserEmail::find()->guid($this->email->getGUID())->confirmed(UserEmail::$confirmFalse)->one());
        
        $this->email->confirmation = UserEmail::$confirmTrue;
        $this->assertTrue($this->email->save());
        $this->assertInstanceOf(UserEmail::class, UserEmail::find()->guid($this->email->getGUID())->confirmed()->one());
        $this->assertNull(UserEmail::find()->guid($this->email->getGUID())->confirmed(UserEmail::$confirmFalse)->one());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group blameable
     * @group email
     */
    public function testDescription()
    {
        $this->assertTrue($this->user->register([$this->email]));
        $desc = \Yii::$app->security->generateRandomString();
        $this->email->setDescription($desc);
        $this->assertEquals($desc, $this->email->getDescription());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group blameable
     * @group email
     */
    public function testFindOneOrCreate()
    {
        $this->assertTrue($this->user->register([$this->email]));
        $faker = $this->faker->email;
        $email = $this->user->findOneOrCreate(UserEmail::class, ['email' => $this->email->email], ['email' => $faker]);
        /* @var $email UserEmail */
        $this->assertInstanceOf(UserEmail::class, $email);
        $this->assertFalse($email->getIsNewRecord());
        $this->assertNotEquals($faker, $email->email);
        
        $this->assertGreaterThanOrEqual(1, $email->delete());
        $faker = $this->faker->email;
        $email = $this->user->findOneOrCreate(UserEmail::class, ['email' => $this->email->email], ['email' => $faker]);
        $this->assertInstanceOf(UserEmail::class, $email);
        $this->assertTrue($email->getIsNewRecord());
        $this->assertEquals($faker, $email->email);
        
        $faker = $this->faker->email;
        $email = $this->user->findOneOrCreate(UserEmail::class, ['email' => $this->email->email]);
        $this->assertInstanceOf(UserEmail::class, $email);
        $this->assertTrue($email->getIsNewRecord());
        $this->assertNotEquals($faker, $this->email->email);
        $this->assertNotEquals($faker, $email->email);
        
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group blameable
     * @group email
     */
    public function testUniqueId()
    {
        $this->assertTrue($this->user->register([$this->email]));
        
        $email = $this->user->create(UserEmail::class, ['email' => $this->faker->email]);
        $this->assertNotEquals($email->getGUID(), $this->email->getGUID());
        $email->setID($this->email->getID());
        $this->assertEquals($email->getID(), $this->email->getID());
        try {
            $email->save();
            $this->fail();
        } catch (\Exception $ex) {
        }
        
        $this->assertTrue($this->other->register());
        $email->host = $this->other;
        $this->assertTrue($email->save());
        
        $email->host = $this->user;
        try {
            $email->save();
            $this->fail();
        } catch (\Exception $ex) {
        }
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group blameable
     * @group email
     */
    public function testCanBeEdited()
    {
        try {
            $this->email->getContentCanBeEdited();
            $this->fail();
        } catch (\Exception $ex) {
            $this->assertInstanceOf(\yii\base\NotSupportedException::class, $ex);
        }
    }
}
