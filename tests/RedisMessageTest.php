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

namespace rhosocial\base\models\tests;

use rhosocial\base\models\tests\data\ar\RedisMessage;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class RedisMessageTest extends TestCase
{
    /**
     * @group redis
     * @group message
     */
    public function testNew()
    {
        $user = RedisBlameableTest::prepareUser();
        $other = RedisBlameableTest::prepareUser();
        $message = $user->create(RedisMessage::className(), ['content' => 'message', 'other_guid' => $other->guid]);
        if ($message->save()) {
            $this->assertTrue(true);
        } else {
            var_dump($message->errors);
            $this->fail();
        }
        $this->assertEquals(1, $message->delete());
        $this->assertTrue($user->deregister());
        $this->assertTrue($other->deregister());
    }
    /**
     * @group redis
     * @group message
     * @depends testNew
     */
    public function testExpired()
    {
        $user = RedisBlameableTest::prepareUser();
        $other = RedisBlameableTest::prepareUser();
        $message = $user->create(RedisMessage::className(), ['content' => 'message', 'other_guid' => $other->guid]);
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
        $this->assertTrue($user->deregister());
        $this->assertTrue($other->deregister());
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
        var_dump($sender->offsetDatetime(-(int) $sender->expiredAt));
        var_dump($sender->createdAt);
        $this->fail("The message model has been removed if you meet this message.\n"
            . "This event should not be triggered.");
    }
    public $isRead = false;
    public $isReceived = false;
    /**
     * @group redis
     * @group message
     * @depends testExpired
     */
    public function testRead()
    {
        $user = RedisBlameableTest::prepareUser();
        $other = RedisBlameableTest::prepareUser();
        $message = $user->create(RedisMessage::className(), ['content' => 'message', 'other_guid' => $other->guid]);
        $this->assertTrue($message->save());
        $message_id = $message->guid;
        $this->assertEquals(0, RedisMessage::find()->byIdentity($user)->read()->count());
        $this->assertEquals(1, RedisMessage::find()->byIdentity($user)->unread()->count());
        $this->assertEquals(0, RedisMessage::find()->byIdentity($other)->read()->count());
        $this->assertEquals(0, RedisMessage::find()->byIdentity($other)->unread()->count());
        $this->assertEquals(0, RedisMessage::find()->recipients($user->guid)->read()->count());
        $this->assertEquals(0, RedisMessage::find()->recipients($user->guid)->unread()->count());
        $this->assertEquals(0, RedisMessage::find()->recipients($other->guid)->read()->count());
        $this->assertEquals(1, RedisMessage::find()->recipients($other->guid)->unread()->count());
        $message = RedisMessage::find()->byIdentity($user)->one();
        $message1 = RedisMessage::find()->guid($message_id)->one();
        
        $this->assertInstanceOf(RedisMessage::className(), $message);
        $this->assertInstanceOf(RedisMessage::className(), $message1);
        $this->assertEquals($message->guid, $message1->guid);
        $this->assertFalse($message->isExpired);
        $this->assertFalse($message1->isExpired);
        
        $message->on(RedisMessage::$eventMessageReceived, [$this, 'onReceived']);
        $message->on(RedisMessage::$eventMessageRead, [$this, 'onRead']);
        $message->on(RedisMessage::$eventExpiredRemoved, [$this, 'onShouldNotBeExpiredRemoved']);
        
        $message->content = 'new message';
        $this->assertTrue($message->save());
        $this->assertFalse($this->isReceived);
        $this->assertFalse($this->isRead);
        $this->assertEquals('message', $message->content);
        if ($message->hasBeenRead()) {
            var_dump(RedisMessage::$initDatetime);
            var_dump($message->readAt);
            var_dump(RedisMessage::$initDatetime == $message->readAt);
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
        $this->assertTrue($user->deregister());
        $this->assertTrue($other->deregister());
    }
    /**
     * @group redis
     * @group message
     * @depends testRead
     */
    public function testReceived()
    {
        $user = RedisBlameableTest::prepareUser();
        $other = RedisBlameableTest::prepareUser();
        $message = $user->create(RedisMessage::className(), ['content' => 'message', 'other_guid' => $other->guid]);
        $this->assertTrue($message->save());
        $message_id = $message->guid;
        $this->assertEquals(0, RedisMessage::find()->byIdentity($user)->received()->count());
        $this->assertEquals(1, RedisMessage::find()->byIdentity($user)->unreceived()->count());
        $this->assertEquals(0, RedisMessage::find()->byIdentity($other)->received()->count());
        $this->assertEquals(0, RedisMessage::find()->byIdentity($other)->unreceived()->count());
        $this->assertEquals(0, RedisMessage::find()->recipients($user->guid)->received()->count());
        $this->assertEquals(0, RedisMessage::find()->recipients($user->guid)->unreceived()->count());
        $this->assertEquals(0, RedisMessage::find()->recipients($other->guid)->received()->count());
        $this->assertEquals(1, RedisMessage::find()->recipients($other->guid)->unreceived()->count());
        $message = RedisMessage::find()->recipients($other->guid)->one();
        $message1 = RedisMessage::find()->guid($message_id)->one();
        $this->assertEquals($message->guid, $message1->guid);
        $this->assertFalse($message->isExpired);
        $this->assertFalse($message1->isExpired);
        $message->on(RedisMessage::$eventMessageReceived, [$this, 'onReceived']);
        $message->on(RedisMessage::$eventMessageRead, [$this, 'onRead']);
        $message->on(RedisMessage::$eventExpiredRemoved, [$this, 'onShouldNotBeExpiredRemoved']);
        $this->assertInstanceOf(RedisMessage::className(), $message);
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
        $this->assertTrue($user->deregister());
        $this->assertTrue($other->deregister());
    }
}