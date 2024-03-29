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

namespace rhosocial\base\models\tests\user\message;

use rhosocial\base\models\tests\data\ar\User;
use rhosocial\base\models\tests\data\ar\redis\RedisMessage;
use rhosocial\base\models\tests\user\UserTestCase;
use Throwable;
use yii\base\Exception;
use yii\base\ModelEvent;
use yii\db\IntegrityException;
use yii\db\StaleObjectException;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class RedisMessageTest extends UserTestCase
{
    /**
     * @var User
     */
    protected User $other;

    protected function setUp() : void {
        parent::setUp();
        $this->other = new User(['password' => '123456']);
    }

    /**
     * @throws IntegrityException|Throwable
     */
    protected function tearDown() : void {
        $this->other->deregister();
        parent::tearDown();
    }

    /**
     * @group user
     * @group message
     * @group redis
     * @throws IntegrityException
     * @throws \yii\db\Exception
     * @throws StaleObjectException|Throwable
     */
    public function testNew()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        
        $message = $this->user->create(RedisMessage::class, ['content' => 'message', 'recipient' => $this->other]);
        /* @var $message RedisMessage */
        $this->assertTrue($message->save());
        $this->assertEquals(1, $message->delete());
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group redis
     * @group message
     * @throws IntegrityException|Throwable
     */
    public function testExpired()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        
        $message = $this->user->create(RedisMessage::class, ['content' => 'message', 'recipient' => $this->other]);
        /* @var $message RedisMessage */
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

    /**
     * 
     * @param ModelEvent $event
     */
    public function onReceived($event): true
    {
        //echo "Received Event Triggered\n";
        return $this->isReceived = true;
    }

    /**
     * 
     * @param ModelEvent $event
     */
    public function onRead($event): true
    {
        //echo "Read Event Triggered\n";
        return $this->isRead = true;
    }

    public function onShouldNotBeExpiredRemoved($event)
    {
        $sender = $event->sender;
        /* @var $sender RedisMessage */
        var_dump($sender->offsetDatetime(-(int) $sender->expiredAt));
        var_dump($sender->createdAt);
        $this->fail("The message model has been removed if you meet this message.\n"
            . "This event should not be triggered.");
    }

    protected bool $isRead = false;
    protected bool $isReceived = false;

    /**
     * @group user
     * @group redis
     * @group message
     * @throws Exception|Throwable
     */
    public function testRead()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        
        $content = \Yii::$app->security->generateRandomString();
        $message = $this->user->create(RedisMessage::class, ['content' => $content, 'recipient' => $this->other]);
        /* @var $message RedisMessage */
        $this->assertTrue($message->save());
        $message_id = $message->getGUID();

        // sleep(20);
        $this->assertEquals(0, RedisMessage::find()->byIdentity($this->user)->read()->count());
        $this->assertEquals(1, RedisMessage::find()->byIdentity($this->user)->unread()->count());
        $this->assertEquals(0, RedisMessage::find()->byIdentity($this->other)->read()->count());
        $this->assertEquals(0, RedisMessage::find()->byIdentity($this->other)->unread()->count());
        $this->assertEquals(0, RedisMessage::find()->recipients($this->user->getGUID())->read()->count());
        $this->assertEquals(0, RedisMessage::find()->recipients($this->user->getGUID())->unread()->count());
        $this->assertEquals(0, RedisMessage::find()->recipients($this->other->getGUID())->read()->count());
        $this->assertEquals(1, RedisMessage::find()->recipients($this->other->getGUID())->unread()->count());
        
        $message = RedisMessage::find()->byIdentity($this->user)->one();
        $message1 = RedisMessage::find()->guid($message_id)->one();
        
        $this->assertInstanceOf(RedisMessage::class, $message);
        $this->assertInstanceOf(RedisMessage::class, $message1);
        $this->assertEquals($message->getGUID(), $message1->getGUID());
        $this->assertFalse($message->isExpired);
        $this->assertFalse($message1->isExpired);
        
        $message->on(RedisMessage::EVENT_MESSAGE_RECEIVED, [$this, 'onReceived']);
        $message->on(RedisMessage::EVENT_MESSAGE_READ, [$this, 'onRead']);
        $message->on(RedisMessage::EVENT_EXPIRED_REMOVED, [$this, 'onShouldNotBeExpiredRemoved']);
        
        $message->content = "new $content";
        $this->assertTrue($message->save());
        $this->assertFalse($this->isReceived);
        $this->assertFalse($this->isRead);
        
        $this->assertEquals($content, $message->content);
        
        if ($message->hasBeenRead()) {
            var_dump(RedisMessage::INIT_DATETIME);
            var_dump($message->readAt);
            var_dump(RedisMessage::INIT_DATETIME == $message->readAt);
            $this->fail("The message has not been read yet.");
        } else {
            $this->assertTrue(true);
        }
        
        $this->assertFalse($message->hasBeenReceived());
        
        if ($message->touchRead() && $message->save()) {
            $this->assertTrue(true);
            $this->assertTrue($message->hasBeenReceived());
            $this->assertTrue($message->hasBeenRead());
            if ($this->isReceived) {
                $this->assertTrue(true);
            } else {
                var_dump($message->isAttributeChanged($message->receivedAtAttribute));
                $this->fail();
            }
            if ($this->isRead) {
                $this->assertTrue(true);
            } else {
                var_dump($message->isAttributeChanged($message->readAtAttribute));
                $this->fail();
            }
        } else {
            var_dump($message->errors);
            $this->fail();
        }
        $this->assertEquals(1, $message->delete());
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group redis
     * @group message
     * @throws Exception|Throwable
     */
    public function testReceived()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());

        $content = \Yii::$app->security->generateRandomString();
        $message = $this->user->create(RedisMessage::class, ['content' => $content, 'recipient' => $this->other]);
        /* @var $message RedisMessage */
        $this->assertTrue($message->save());
        $message_id = $message->getGUID();

        //sleep(20);
        $this->assertEquals(0, RedisMessage::find()->byIdentity($this->user)->received()->count());
        $this->assertEquals(1, RedisMessage::find()->byIdentity($this->user)->unreceived()->count());
        $this->assertEquals(0, RedisMessage::find()->byIdentity($this->other)->received()->count());
        $this->assertEquals(0, RedisMessage::find()->byIdentity($this->other)->unreceived()->count());
        $this->assertEquals(0, RedisMessage::find()->recipients($this->user->getGUID())->received()->count());
        $this->assertEquals(0, RedisMessage::find()->recipients($this->user->getGUID())->unreceived()->count());
        $this->assertEquals(0, RedisMessage::find()->recipients($this->other->getGUID())->received()->count());
        $this->assertEquals(1, RedisMessage::find()->recipients($this->other->getGUID())->unreceived()->count());
        
        $message = RedisMessage::find()->recipients($this->other->getGUID())->one();
        $message1 = RedisMessage::find()->guid($message_id)->one();
        $this->assertEquals($message->getGUID(), $message1->getGUID());
        $this->assertFalse($message->isExpired);
        $this->assertFalse($message1->isExpired);
        
        $message->on(RedisMessage::EVENT_MESSAGE_RECEIVED, [$this, 'onReceived']);
        $message->on(RedisMessage::EVENT_MESSAGE_READ, [$this, 'onRead']);
        $message->on(RedisMessage::EVENT_EXPIRED_REMOVED, [$this, 'onShouldNotBeExpiredRemoved']);
        
        $this->assertInstanceOf(RedisMessage::class, $message);
        $this->assertFalse($message->hasBeenReceived());
        $this->assertFalse($message->hasBeenRead());
        
        if ($message->touchReceived() && $message->save()) {
            $this->assertTrue(true);
            $this->assertTrue($message->hasBeenReceived());
            $this->assertFalse($message->hasBeenRead());
            if ($this->isReceived) {
                $this->assertTrue(true);
            } else {
                var_dump($message->isAttributeChanged($message->receivedAtAttribute));
                $this->fail();
            }
            $this->assertFalse($this->isRead);
        } else {
            var_dump($message->errors);
            $this->fail();
        }
        $this->assertEquals(1, $message->delete());
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group redis
     * @group message
     * @throws Exception|Throwable
     */
    public function testGetUpdater()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        
        $content = \Yii::$app->security->generateRandomString();
        $message = $this->user->create(RedisMessage::class, ['content' => $content, 'recipient' => $this->other]);
        /* @var $message RedisMessage */
        $this->assertTrue($message->save());
        
        $this->assertNull($message->getUpdater());
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group redis
     * @group message
     * @throws Exception|Throwable
     */
    public function testSetUpdater()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        
        $content = \Yii::$app->security->generateRandomString();
        $message = $this->user->create(RedisMessage::class, ['content' => $content, 'recipient' => $this->other]);
        /* @var $message RedisMessage */
        $this->assertTrue($message->save());
        
        $this->assertFalse($message->setUpdater($this->other));
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group redis
     * @group message
     * @throws Exception|Throwable
     */
    public function testPagination()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());

        $content = \Yii::$app->security->generateRandomString();
        $message = $this->user->create(RedisMessage::class, ['content' => $content, 'recipient' => $this->other]);
        /* @var $message RedisMessage */
        $this->assertTrue($message->save());
        $pagination = RedisMessage::getPagination();
        //sleep(20);
        $this->assertEquals(1, $pagination->limit);
        $this->assertEquals(1, $pagination->totalCount);
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }
}
