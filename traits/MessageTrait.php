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

namespace rhosocial\base\models\traits;

use yii\base\ModelEvent;
use yii\db\AfterSaveEvent;

/**
 * This trait should be used in models extended from models used BlameableTrait.
 * Notice: The models used [[BlameableTrait]] are also models used [[EntityTrait]].
 *
 * @property-read array $messageRules
 * @property mixed $readAt
 * @property mixed $receivedAt
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
trait MessageTrait
{
    use MutualTrait;
    
    public string|false $attachmentAttribute = 'attachment';
    public string|false $receivedAtAttribute = 'received_at';
    public string|false $readAtAttribute = 'read_at';
    const EVENT_MESSAGE_RECEIVED = 'messageReceived';
    const EVENT_MESSAGE_READ = 'messageRead';
    public bool $permitChangeContent = false;
    public bool $permitChangeReceivedAt = false;
    public bool $permitChangeReadAt = false;
    
    /**
     * Whether the message has been received.
     * Note: This trait should be used for models which use [[TimestampTrait]].
     * @return bool
     */
    public function hasBeenReceived(): bool
    {
        return is_string($this->receivedAtAttribute) && !$this->isInitDatetime($this->getReceivedAt());
    }
    
    /**
     * Whether the message has been read.
     * If a message has been read, it must have been received.
     * Note: This trait should be used for models which use [[TimestampTrait]].
     * @return bool
     */
    public function hasBeenRead(): bool
    {
        return is_string($this->readAtAttribute) && !$this->isInitDatetime($this->getReadAt());
    }
    
    public function touchReceived()
    {
        return $this->setReceivedAt(static::currentDatetime());
    }
    
    public function touchRead()
    {
        return $this->setReadAt(static::currentDatetime());
    }
    
    public function getReceivedAt()
    {
        if (is_string($this->receivedAtAttribute) && !empty($this->receivedAtAttribute)) {
            $raAttribute = $this->receivedAtAttribute;
            return $this->$raAttribute;
        }
        return null;
    }
    
    public function setReceivedAt($receivedAt)
    {
        if (is_string($this->receivedAtAttribute) && !empty($this->receivedAtAttribute)) {
            $raAttribute = $this->receivedAtAttribute;
            return $this->$raAttribute = $receivedAt;
        }
        return null;
    }
    
    public function getReadAt()
    {
        if (is_string($this->readAtAttribute) && !empty($this->readAtAttribute)) {
            $raAttribute = $this->readAtAttribute;
            return $this->$raAttribute;
        }
        return null;
    }
    
    public function setReadAt($readAt)
    {
        if (is_string($this->readAtAttribute) && !empty($this->readAtAttribute)) {
            $raAttribute = $this->readAtAttribute;
            return $this->$raAttribute = $readAt;
        }
        return null;
    }
    
    /**
     *
     * @param ModelEvent $event
     */
    public function onInitReceivedAtAttribute($event): void
    {
        $sender = $event->sender;
        /* @var $sender static */
        $sender->setReceivedAt(static::getInitDatetime($event));
    }
    
    /**
     *
     * @param ModelEvent $event
     */
    public function onInitReadAtAttribute($event): void
    {
        $sender = $event->sender;
        /* @var $sender static */
        $sender->setReadAt(static::getInitDatetime($event));
    }
    
    /**
     * We consider you have received the message if you read it.
     * @param ModelEvent $event
     */
    public function onReadAtChanged($event): void
    {
        $sender = $event->sender;
        /* @var $sender static */
        $raAttribute = $sender->readAtAttribute;
        if (!is_string($raAttribute) || empty($raAttribute)) {
            return;
        }
        $reaAttribute = $sender->receivedAtAttribute;
        if (is_string($reaAttribute) && !$sender->isInitDatetime($sender->$raAttribute) && $sender->isInitDatetime($sender->$reaAttribute)) {
            $sender->$reaAttribute = $sender->currentDatetime();
        }
        // If it is permitted to change read time, it will return directly.
        if ($sender->permitChangeReadAt) {
            return;
        }
        $oldRa = $sender->getOldAttribute($raAttribute);
        if ($oldRa != null && !$sender->isInitDatetime($oldRa) && $sender->$raAttribute != $oldRa) {
            $sender->$raAttribute = $oldRa;
        }
    }
    
    /**
     * You are not allowed to change receiving time if you have received it.
     * @param ModelEvent $event
     */
    public function onReceivedAtChanged($event): void
    {
        $sender = $event->sender;
        $raAttribute = $sender->receivedAtAttribute;
        if (!is_string($raAttribute) || empty($raAttribute)) {
            return;
        }
        // If it is permitted to change receiving time, then it will return directly.
        if ($sender->permitChangeReceivedAt) {
            return;
        }
        $oldRa = $sender->getOldAttribute($raAttribute);
        if ($oldRa != null && !$sender->isInitDatetime($oldRa) && $sender->$raAttribute != $oldRa) {
            $sender->$raAttribute = $oldRa;
        }
    }
    
    /**
     * You are not allowed to change the content if it is not new message.
     * @param ModelEvent $event
     */
    public function onContentChanged($event): void
    {
        $sender = $event->sender;
        // If it is permitted to change content, then it will return directly.
        if ($sender->permitChangeContent) {
            return;
        }
        // The message will be reversed if it changed (current message isn't
        // same as the old).
        $cAttribute = $sender->contentAttribute;
        $oldContent = $sender->getOldAttribute($cAttribute);
        if ($oldContent != $sender->$cAttribute) {
            $sender->$cAttribute = $oldContent;
        }
    }
    
    /**
     *
     * @param AfterSaveEvent $event
     */
    public function onMessageUpdated($event): void
    {
        $sender = $event->sender;
        /* @var $sender static */
        $reaAttribute = $sender->receivedAtAttribute;
        if (isset($event->changedAttributes[$reaAttribute]) && $event->changedAttributes[$reaAttribute] != $sender->$reaAttribute) {
            $sender->trigger(static::EVENT_MESSAGE_RECEIVED);
        }
        $raAttribute = $sender->readAtAttribute;
        if (isset($event->changedAttributes[$raAttribute]) && $event->changedAttributes[$raAttribute] != $sender->$raAttribute) {
            $sender->trigger(static::EVENT_MESSAGE_READ);
        }
    }
    
    /**
     *
     */
    public function initMessageEvents(): void
    {
        $this->on(static::EVENT_BEFORE_INSERT, [$this, 'onInitReceivedAtAttribute']);
        $this->on(static::EVENT_BEFORE_INSERT, [$this, 'onInitReadAtAttribute']);
        $this->on(static::EVENT_BEFORE_UPDATE, [$this, 'onReceivedAtChanged']);
        $this->on(static::EVENT_BEFORE_UPDATE, [$this, 'onReadAtChanged']);
        $this->on(static::EVENT_BEFORE_UPDATE, [$this, 'onContentChanged']);
        $this->on(static::EVENT_AFTER_UPDATE, [$this, 'onMessageUpdated']);
    }
    
    /**
     * Return rules associated with message.
     * @return array
     */
    public function getMessageRules(): array
    {
        $rules = [];
        $rules = array_merge($rules, $this->getMutualRules());
        if (is_string($this->attachmentAttribute) && !empty($this->attachmentAttribute)) {
            $rules[] = [$this->attachmentAttribute, 'safe'];
        }
        if (is_string($this->receivedAtAttribute) && !empty($this->receivedAtAttribute)) {
            $rules[] = [$this->receivedAtAttribute, 'safe'];
        }
        if (is_string($this->readAtAttribute) && !empty($this->readAtAttribute)) {
            $rules[] = [$this->readAtAttribute, 'safe'];
        }
        return $rules;
    }
    
    /**
     * @inheritdoc
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), $this->getMessageRules());
    }
    
    /**
     * @inheritdoc
     * @return array
     */
    public function enabledFields(): array
    {
        $fields = parent::enabledFields();
        if (is_string($this->otherGuidAttribute) && !empty($this->otherGuidAttribute)) {
            $fields[] = $this->otherGuidAttribute;
        }
        if (is_string($this->attachmentAttribute) && !empty($this->attachmentAttribute)) {
            $fields[] = $this->attachmentAttribute;
        }
        if (is_string($this->receivedAtAttribute) && !empty($this->receivedAtAttribute)) {
            $fields[] = $this->receivedAtAttribute;
        }
        if (is_string($this->readAtAttribute) && !empty($this->readAtAttribute)) {
            $fields[] = $this->readAtAttribute;
        }
        return $fields;
    }
}
