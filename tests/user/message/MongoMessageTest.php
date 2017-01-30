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

namespace rhosocial\base\models\tests\user\message;

use rhosocial\base\models\tests\data\ar\User;
use rhosocial\base\models\tests\data\ar\mongodb\MongoMessage;
use rhosocial\base\models\tests\MongoTestCase;

/**
 * @author vistart <i@vistart.me>
 */
class MongoMessageTest extends MongoTestCase
{
    /**
     *
     * @var User
     */
    protected $user;
    
    /**
     * @var User
     */
    protected $other;
    
    protected function setUp()
    {
        parent::setUp();
        $this->user = new User();
        \Yii::$app->user->identity = $this->user;
        $this->other = new User(['password' => '123456']);
    }
    
    protected function tearDown()
    {
        $this->other->deregister();
        if ($this->user instanceof User) {
            try {
                $this->user->deregister();
            } catch (\Exception $ex) {

            } finally {
                $this->user = null;
            }
        }
        User::deleteAll();
        parent::tearDown();
    }
    
    /**
     * @group user
     * @group message
     * @group mongo
     */
    public function testNew()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        
        $message = $this->user->create(MongoMessage::class, ['content' => 'message', 'recipient' => $this->other]);
        /* @var $message MongoMessage */
        $this->assertTrue($message->save());
        $this->assertEquals(1, $message->delete());
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group user
     * @group mongo
     * @group message
     */
    public function testExpired()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        
        $message = $this->user->create(MongoMessage::class, ['content' => 'message', 'recipient' => $this->other]);
        /* @var $message MongoMessage */
        $this->assertTrue($message->save());
        if ($message->isExpired) {
            echo "time format: ";
            var_dump($message->timeFormat);
            echo "created at: ";
            var_dump($message->createdAt);
            echo "expired at: ";
            var_dump($message->expiredAt);
            $this->fail("The message should not be expired.");
        } else {
            $this->assertTrue(true);
        }
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }
}