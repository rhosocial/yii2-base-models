<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2023 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\traits;

use Yii;
use yii\base\Exception;
use yii\base\ModelEvent;
use yii\base\NotSupportedException;

/**
 * This trait allow its owner to enable the entity to be blamed by user.
 * @property-read boolean $isConfirmed
 * @property integer $confirmation
 * @property-read array $confirmationRules
 * @property string $confirmCode the confirmation code used for confirming the content.
 * You can disable this attribute and create a new model for storing confirm code as
 * its low-frequency usage.
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
trait ConfirmationTrait
{
    const CONFIRMATION_STATUS_UNCONFIRMED = 0;
    const CONFIRMATION_STATUS_CONFIRMED = 1;

    /**
     * @var string|false attribute name of confirmation, or false if disable confirmation features.
     */
    public string|false $confirmationAttribute = false;

    /**
     * @var string|false This attribute specify the name of confirm_code attribute, if
     * this attribute is assigned to false, this feature will be ignored.
     * if $confirmationAttribute is empty or false, this attribute will be skipped.
     */
    public string|false $confirmCodeAttribute = 'confirm_code';

    /**
     * @var int The expiration in seconds. If $confirmCodeAttribute is
     * specified, this attribute must be specified.
     */
    public int $confirmCodeExpiration = 3600;

    /**
     * @var string This attribute specify the name of confirm_time attribute. if
     * this attribute is assigned to false, this feature will be ignored.
     * if $confirmationAttribute is empty or false, this attribute will be skipped.
     */
    public string $confirmTimeAttribute = 'confirmed_at';

    /**
     * @var string initialization confirm time.
     */
    public string $initConfirmTime = '1970-01-01 00:00:00';

    const EVENT_CONFIRMATION_CHANGED = 'confirmationChanged';
    const EVENT_CONFIRMATION_CANCELED = 'confirmationCanceled';
    const EVENT_CONFIRMATION_SUCCEEDED = 'confirmationSucceeded';

    /**
     * Apply confirmation.
     * @return boolean
     * @throws NotSupportedException|Exception
     */
    public function applyConfirmation(): bool
    {
        if (empty($this->confirmCodeAttribute)) {
            throw new NotSupportedException('This method is not implemented.');
        }
        $this->setConfirmCode($this->generateConfirmationCode());
        return $this->save();
    }

    /**
     * Set confirm code.
     * @param string $code
     */
    public function setConfirmCode(string $code): void
    {
        if (empty($this->confirmCodeAttribute)) {
            return;
        }
        $confirmCodeAttribute = $this->confirmCodeAttribute;
        $this->$confirmCodeAttribute = $code;
        if (!$this->confirmTimeAttribute) {
            return;
        }
        $confirmTimeAttribute = $this->confirmTimeAttribute;
        if (!empty($code)) {
            $this->$confirmTimeAttribute = date('Y-m-d H:i:s');
            return;
        }
        $this->$confirmTimeAttribute = $this->initConfirmTime;
    }

    /**
     * Get confirm code.
     * @return string|null
     */
    public function getConfirmCode(): ?string
    {
        $confirmCodeAttribute = $this->confirmCodeAttribute;
        return (!empty($confirmCodeAttribute)) ? $this->$confirmCodeAttribute : null;
    }

    /**
     * Confirm the current content.
     * @param string $code
     * @return bool
     */
    public function confirm(string $code = ''): bool
    {
        if (!$this->confirmationAttribute || !$this->validateConfirmationCode($code)) {
            return false;
        }
        $this->confirmation = self::CONFIRMATION_STATUS_CONFIRMED;
        return $this->save();
    }

    /**
     * Generate confirmation code.
     * @return string code
     * @throws Exception
     */
    public function generateConfirmationCode(): string
    {
        return substr(sha1(Yii::$app->security->generateRandomString()), 0, 17);
    }

    /**
     * Validate the confirmation code.
     * @param string $code
     * @return bool Whether the confirmation code is valid.
     */
    public function validateConfirmationCode(string $code): bool
    {
        $ccAttribute = $this->confirmCodeAttribute;
        if (empty($ccAttribute)) {
            return true;
        }
        return $this->$ccAttribute === $code;
    }

    /**
     * Get confirmation status of current model.
     * @return bool Whether current model has been confirmed.
     */
    public function getIsConfirmed(): bool
    {
        $cAttribute = $this->confirmationAttribute;
        return !(is_string($cAttribute) && !empty($cAttribute)) || $this->$cAttribute > self::CONFIRMATION_STATUS_UNCONFIRMED;
    }

    /**
     * Initialize the confirmation status.
     * This method is ONLY used for being triggered by event. DO NOT call,
     * override or modify it directly, unless you know the consequences.
     * @param ModelEvent $event
     */
    public function onInitConfirmation($event): void
    {
        $sender = $event->sender;
        /* @var $sender static */
        if (empty($sender->confirmationAttribute)) {
            return;
        }
        $sender->confirmation = self::CONFIRMATION_STATUS_UNCONFIRMED;
        $sender->confirmCode = '';
    }

    /**
     * Set confirmation.
     * @param mixed $value
     */
    public function setConfirmation(mixed $value): void
    {
        $cAttribute = $this->confirmationAttribute;
        if (empty($cAttribute)) {
            return;
        }
        $this->$cAttribute = $value;
        $this->trigger(self::EVENT_CONFIRMATION_CHANGED);
    }

    /**
     * Get confirmation.
     * @return mixed
     */
    public function getConfirmation(): mixed
    {
        $cAttribute = $this->confirmationAttribute;
        return (is_string($cAttribute) && !empty($cAttribute)) ? $this->$cAttribute : null;
    }

    /**
     * When confirmation status changed, this event will be triggered. If
     * confirmation succeeded, the confirm_time will be assigned to current time,
     * or the confirm_time will be assigned to initConfirmTime.
     * This method is ONLY used for being triggered by event. DO NOT call,
     * override or modify it directly, unless you know the consequences.
     * @param ModelEvent $event
     */
    public function onConfirmationChanged($event): void
    {
        $sender = $event->sender;
        $cAttribute = $sender->confirmationAttribute;
        if (empty($cAttribute)) {
            return;
        }
        if ($sender->isAttributeChanged($cAttribute)) {
            $sender->confirmCode = '';
            if ($sender->$cAttribute == self::CONFIRMATION_STATUS_UNCONFIRMED) {
                $sender->trigger(self::EVENT_CONFIRMATION_CANCELED);
                return;
            }
            $sender->trigger(self::EVENT_CONFIRMATION_SUCCEEDED);
            $sender->resetOthersConfirmation();
        }
    }

    /**
     * Get rules associated with confirmation attributes.
     * if not enable confirmation feature, it will return empty array.
     * @return array
     */
    public function getConfirmationRules(): array
    {
        if (!$this->confirmationAttribute) {
            return [];
        }
        return [
            [[$this->confirmationAttribute], 'number', 'integerOnly' => true, 'min' => 0],
            [[$this->confirmTimeAttribute], 'safe'],
        ];
    }

    /**
     * When the content changed, reset confirmation status.
     */
    protected function resetConfirmation(): void
    {
        $contentAttribute = $this->contentAttribute;
        if (empty($contentAttribute)) {
            return;
        }
        if (is_array($contentAttribute)) {
            foreach ($contentAttribute as $attribute) {
                if ($this->isAttributeChanged($attribute)) {
                    $this->confirmation = self::CONFIRMATION_STATUS_UNCONFIRMED;
                    break;
                }
            }
        } elseif ($this->isAttributeChanged($contentAttribute)) {
            $this->confirmation = self::CONFIRMATION_STATUS_UNCONFIRMED;
        }
    }

    /**
     * Reset others' confirmation when the others own the same content.
     */
    protected function resetOthersConfirmation(): void
    {
        if (!$this->confirmationAttribute || empty($this->hostClass)) {
            return;
        }
        $contents = static::find()
            ->where([$this->contentAttribute => $this->getContent()])
            ->andWhere(['not like', $this->createdByAttribute, $this->user->getGUID()])
            ->all();
        foreach ($contents as $content) {
            $content->confirmation = self::CONFIRMATION_STATUS_UNCONFIRMED;
            $content->save();
        }
    }
}
