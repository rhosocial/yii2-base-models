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
 * @version 1.0
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

    /**
     * 
     * @param \yii\base\ModelEvent $event
     */
    public function onReceived($event)
    {
        //echo "Received Event Triggered\n";
        return $this->isReceived = true;
    }

    /**
     * 
     * @param \yii\base\ModelEvent $event
     */
    public function onRead($event)
    {
        //echo "Read Event Triggered\n";
        return $this->isRead = true;
    }

    public function onShouldNotBeExpiredRemoved($event)
    {
        $sender = $event->sender;
        /* @var $sender MongoMessage */
        var_dump($sender->offsetDatetime(-(int) $sender->expiredAt));
        var_dump($sender->createdAt);
        $this->fail("The message model has been removed if you meet this message.\n"
            . "This event should not be triggered.");
    }

    protected $isRead = false;
    protected $isReceived = false;

    /**
     * @group user
     * @group mongo
     * @group message
     */
    public function testRead()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        
        $content = \Yii::$app->security->generateRandomString();
        $message = $this->user->create(MongoMessage::class, ['content' => $content, 'recipient' => $this->other]);
        /* @var $message MongoMessage */
        $this->assertTrue($message->save());
        $message_id = $message->getGUID();
        
        sleep(1);
        $this->assertEquals(0, MongoMessage::find()->byIdentity($this->user)->read()->count());
        $this->assertEquals(1, MongoMessage::find()->byIdentity($this->user)->unread()->count());
        $this->assertEquals(0, MongoMessage::find()->byIdentity($this->other)->read()->count());
        $this->assertEquals(0, MongoMessage::find()->byIdentity($this->other)->unread()->count());
        $this->assertEquals(0, MongoMessage::find()->recipients($this->user->getGUID())->read()->count());
        $this->assertEquals(0, MongoMessage::find()->recipients($this->user->getGUID())->unread()->count());
        $this->assertEquals(0, MongoMessage::find()->recipients($this->other->getGUID())->read()->count());
        $this->assertEquals(1, MongoMessage::find()->recipients($this->other->getGUID())->unread()->count());
        
        $message = MongoMessage::find()->byIdentity($this->user)->one();
        $message1 = MongoMessage::find()->guid($message_id)->one();
        
        $this->assertInstanceOf(MongoMessage::class, $message);
        $this->assertInstanceOf(MongoMessage::class, $message1);
        $this->assertEquals($message->getGUID(), $message1->getGUID());
        $this->assertFalse($message->isExpired);
        $this->assertFalse($message1->isExpired);
        
        $message->on(MongoMessage::$eventMessageReceived, [$this, 'onReceived']);
        $message->on(MongoMessage::$eventMessageRead, [$this, 'onRead']);
        $message->on(MongoMessage::$eventExpiredRemoved, [$this, 'onShouldNotBeExpiredRemoved']);
        
        $message->content = "new $content";
        $this->assertTrue($message->save());
        $this->assertFalse($this->isReceived);
        $this->assertFalse($this->isRead);
        
        $this->assertEquals($content, $message->content);
        
        if ($message->hasBeenRead()) {
            var_dump(MongoMessage::$initDatetime);
            var_dump($message->readAt);
            var_dump(MongoMessage::$initDatetime == $message->readAt);
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
     * @group mongo
     * @group message
     */
    public function testReceived()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        
        $content = \Yii::$app->security->generateRandomString();
        $message = $this->user->create(MongoMessage::class, ['content' => $content, 'recipient' => $this->other]);
        /* @var $message MongoMessage */
        $this->assertTrue($message->save());
        $message_id = $message->getGUID();
        
        $this->assertEquals(0, MongoMessage::find()->byIdentity($this->user)->received()->count());
        $this->assertEquals(1, MongoMessage::find()->byIdentity($this->user)->unreceived()->count());
        $this->assertEquals(0, MongoMessage::find()->byIdentity($this->other)->received()->count());
        $this->assertEquals(0, MongoMessage::find()->byIdentity($this->other)->unreceived()->count());
        $this->assertEquals(0, MongoMessage::find()->recipients($this->user->getGUID())->received()->count());
        $this->assertEquals(0, MongoMessage::find()->recipients($this->user->getGUID())->unreceived()->count());
        $this->assertEquals(0, MongoMessage::find()->recipients($this->other->getGUID())->received()->count());
        $this->assertEquals(1, MongoMessage::find()->recipients($this->other->getGUID())->unreceived()->count());
        
        $message = MongoMessage::find()->recipients($this->other->getGUID())->one();
        $message1 = MongoMessage::find()->guid($message_id)->one();
        $this->assertEquals($message->getGUID(), $message1->getGUID());
        $this->assertFalse($message->isExpired);
        $this->assertFalse($message1->isExpired);
        
        $message->on(MongoMessage::$eventMessageReceived, [$this, 'onReceived']);
        $message->on(MongoMessage::$eventMessageRead, [$this, 'onRead']);
        $message->on(MongoMessage::$eventExpiredRemoved, [$this, 'onShouldNotBeExpiredRemoved']);
        
        $this->assertInstanceOf(MongoMessage::class, $message);
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
     * @group mongo
     * @group message
     */
    public function testGetUpdater()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        
        $content = \Yii::$app->security->generateRandomString();
        $message = $this->user->create(MongoMessage::class, ['content' => $content, 'recipient' => $this->other]);
        /* @var $message MongoMessage */
        $this->assertTrue($message->save());
        
        $this->assertNull($message->getUpdater());
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group mongo
     * @group message
     */
    public function testSetUpdater()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        
        $content = \Yii::$app->security->generateRandomString();
        $message = $this->user->create(MongoMessage::class, ['content' => $content, 'recipient' => $this->other]);
        /* @var $message MongoMessage */
        $this->assertTrue($message->save());
        
        $this->assertFalse($message->setUpdater($this->other));
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group mongo
     * @group message
     */
    public function testPagination()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        
        $content = \Yii::$app->security->generateRandomString();
        $message = $this->user->create(MongoMessage::class, ['content' => $content, 'recipient' => $this->other]);
        /* @var $message MongoMessage */
        $this->assertTrue($message->save());
        
        $pagination = MongoMessage::getPagination();
        /* @var $pagination \yii\data\Pagination */
        $this->assertEquals(1, $pagination->limit);
        $this->assertEquals(1, $pagination->totalCount);
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group mongo
     * @group message
     */
    public function testOpposite()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        
        $content1 = \Yii::$app->security->generateRandomString();
        $message = $this->user->create(MongoMessage::class, ['content' => $content1, 'recipient' => $this->other]);
        /* @var $message MongoMessage */
        $this->assertTrue($message->save());
        
        $content2 = \Yii::$app->security->generateRandomString();
        $reply = $this->other->create(MongoMessage::class, ['content' => $content2, 'recipient' => $this->user]);
        /* @var $reply MongoMessage */
        $this->assertTrue($reply->save());
        
        $m = MongoMessage::find()->opposite($this->user, $this->other);
        $p = MongoMessage::find()->opposite($this->other, $this->user);
        $this->assertInstanceOf(MongoMessage::class, $m);
        $this->assertInstanceOf(MongoMessage::class, $p);
        $this->assertEquals($content1, $p->content);
        $this->assertEquals($content2, $m->content);
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group mongo
     * @group message
     */
    public function testOpposites()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        
        $content1 = \Yii::$app->security->generateRandomString();
        $message = $this->user->create(MongoMessage::class, ['content' => $content1, 'recipient' => $this->other]);
        /* @var $message MongoMessage */
        $this->assertTrue($message->save());
        
        $content2 = \Yii::$app->security->generateRandomString();
        $reply = $this->other->create(MongoMessage::class, ['content' => $content2, 'recipient' => $this->user]);
        /* @var $reply MongoMessage */
        $this->assertTrue($reply->save());
        
        $m = MongoMessage::find()->opposites($this->user, $this->other);
        $p = MongoMessage::find()->opposites($this->other, $this->user);
        $this->assertCount(1, $m);
        $this->assertCount(1, $p);
        $this->assertInstanceOf(MongoMessage::class, $m[0]);
        $this->assertInstanceOf(MongoMessage::class, $p[0]);
        $this->assertEquals($content1, $p[0]->content);
        $this->assertEquals($content2, $m[0]->content);
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group mongo
     * @group message
     */
    public function testInitiators()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        
        $content1 = \Yii::$app->security->generateRandomString();
        $message = $this->user->create(MongoMessage::class, ['content' => $content1, 'recipient' => $this->other]);
        /* @var $message MongoMessage */
        $this->assertTrue($message->save());
        
        $content2 = \Yii::$app->security->generateRandomString();
        $reply = $this->other->create(MongoMessage::class, ['content' => $content2, 'recipient' => $this->user]);
        /* @var $reply MongoMessage */
        $this->assertTrue($reply->save());
        
        $m = MongoMessage::find()->initiators()->initiators($this->user)->one();
        $p = MongoMessage::find()->initiators()->initiators($this->other)->one();
        $this->assertInstanceOf(MongoMessage::class, $m);
        $this->assertInstanceOf(MongoMessage::class, $p);
        $this->assertEquals($content1, $m->content);
        $this->assertEquals($content2, $p->content);
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group mongo
     * @group message
     */
    public function testRecipients()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        
        $content1 = \Yii::$app->security->generateRandomString();
        $message = $this->user->create(MongoMessage::class, ['content' => $content1, 'recipient' => $this->other]);
        /* @var $message MongoMessage */
        $this->assertTrue($message->save());
        
        $content2 = \Yii::$app->security->generateRandomString();
        $reply = $this->other->create(MongoMessage::class, ['content' => $content2, 'recipient' => $this->user]);
        /* @var $reply MongoMessage */
        $this->assertTrue($reply->save());
        
        $p = MongoMessage::find()->recipients()->recipients($this->user)->one();
        $m = MongoMessage::find()->recipients()->recipients($this->other)->one();
        $this->assertInstanceOf(MongoMessage::class, $m);
        $this->assertInstanceOf(MongoMessage::class, $p);
        $this->assertEquals($content1, $m->content);
        $this->assertEquals($content2, $p->content);
        
        $this->assertTrue($m->recipient->equals($this->other));
        $this->assertTrue($p->recipient->equals($this->user));
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group mongo
     * @group message
     */
    public function testFindByUpdater()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        
        $content1 = \Yii::$app->security->generateRandomString();
        $message = $this->user->create(MongoMessage::class, ['content' => $content1, 'recipient' => $this->other]);
        /* @var $message MongoMessage */
        $this->assertTrue($message->save());
        
        $query = MongoMessage::find()->updatedBy($this->user);
        $this->assertEquals($query, MongoMessage::find());
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group mongo
     * @group message
     */
    public function testInvalid()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        
        $content1 = \Yii::$app->security->generateRandomString();
        $message = $this->user->create(MongoMessage::class, ['content' => $content1, 'recipient' => $this->other]);
        /* @var $message MongoMessage */
        $this->assertTrue($message->save());
        
        $message->otherGuidAttribute = false;
        try {
            $message->setRecipient($this->user);
            $this->fail();
        } catch (\Exception $ex) {
            $this->assertInstanceOf(\yii\base\InvalidConfigException::class, $ex);
        }
        
        try {
            $message->getRecipient();
            $this->fail();
        } catch (\Exception $ex) {
            $this->assertInstanceOf(\yii\base\InvalidConfigException::class, $ex);
        }
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }
}
