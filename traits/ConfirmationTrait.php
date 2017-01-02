<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\traits;

use Yii;
use yii\base\ModelEvent;

/**
 * This trait allow its owner to enable the entity to be blamed by user.
 * @property-read boolean $isConfirmed
 * @property integer $confirmation
 * @property-read array $confirmationRules
 * @property string $confirmCode the confirm code used for confirming the content.
 * You can disable this attribute and create a new model for storing confirm code as
 * its low-frequency usage.
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
trait ConfirmationTrait
{

    /**
     * @var int Unconfirmed.
     */
    public static $confirmFalse = 0;

    /**
     * @var int Confirmed.
     */
    public static $confirmTrue = 1;

    /**
     * @var string|false attribute name of confirmation, or false if disable confirmation features.
     */
    public $confirmationAttribute = false;

    /**
     * @var string This attribute specify the name of confirm_code attribute, if
     * this attribute is assigned to false, this feature will be ignored.
     * if $confirmationAttribute is empty or false, this attribute will be skipped.
     */
    public $confirmCodeAttribute = 'confirm_code';

    /**
     * @var integer The expiration in seconds. If $confirmCodeAttribute is
     * specified, this attribute must be specified.
     */
    public $confirmCodeExpiration = 3600;

    /**
     * @var string This attribute specify the name of confirm_time attribute. if
     * this attribute is assigned to false, this feature will be ignored.
     * if $confirmationAttribute is empty or false, this attribute will be skipped.
     */
    public $confirmTimeAttribute = 'confirmed_at';

    /**
     * @var string initialization confirm time.
     */
    public $initConfirmTime = '1970-01-01 00:00:00';
    public static $eventConfirmationChanged = "confirmationChanged";
    public static $eventConfirmationCanceled = "confirmationCanceled";
    public static $eventConfirmationSuceeded = "confirmationSucceeded";

    /**
     * Apply confirmation.
     * @return boolean
     * @throws \yii\base\NotSupportedException
     */
    public function applyConfirmation()
    {
        if (!$this->confirmCodeAttribute) {
            throw new \yii\base\NotSupportedException('This method is not implemented.');
        }
        $this->confirmCode = $this->generateConfirmationCode();
        if (!$this->save()) {
            return false;
        }
    }

    /**
     * Set confirm code.
     * @param string $code
     */
    public function setConfirmCode($code)
    {
        if (!$this->confirmCodeAttribute) {
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
     * @return string
     */
    public function getConfirmCode()
    {
        $confirmCodeAttribute = $this->confirmCodeAttribute;
        return is_string($confirmCodeAttribute) ? $this->$confirmCodeAttribute : null;
    }

    /**
     * Confirm the current content.
     * @param string $code
     * @return boolean
     */
    public function confirm($code)
    {
        if (!$this->confirmationAttribute || !$this->validateConfirmationCode($code)) {
            return false;
        }
        $this->confirmation = self::$confirmTrue;
        return $this->save();
    }

    /**
     * Generate confirmation code.
     * @return string code
     */
    public function generateConfirmationCode()
    {
        return substr(sha1(Yii::$app->security->generateRandomString()), 0, 8);
    }

    /**
     * Validate the confirmation code.
     * @param string $code
     * @return boolean Whether the confirmation code is valid.
     */
    public function validateConfirmationCode($code)
    {
        $ccAttribute = $this->confirmCodeAttribute;
        if (!$ccAttribute) {
            return true;
        }
        return $this->$ccAttribute === $code;
    }

    /**
     * Get confirmation status of current model.
     * @return boolean Whether current model has been confirmed.
     */
    public function getIsConfirmed()
    {
        $cAttribute = $this->confirmationAttribute;
        return is_string($cAttribute) ? $this->$cAttribute > static::$confirmFalse : true;
    }

    /**
     * Initialize the confirmation status.
     * This method is ONLY used for being triggered by event. DO NOT call,
     * override or modify it directly, unless you know the consequences.
     * @param ModelEvent $event
     */
    public function onInitConfirmation($event)
    {
        $sender = $event->sender;
        if (!$sender->confirmationAttribute) {
            return;
        }
        $sender->confirmation = self::$confirmFalse;
        $sender->confirmCode = '';
    }

    /**
     * Set confirmation.
     * @param mixed $value
     */
    public function setConfirmation($value)
    {
        $cAttribute = $this->confirmationAttribute;
        if (!$cAttribute) {
            return;
        }
        $this->$cAttribute = $value;
        $this->trigger(self::$eventConfirmationChanged);
    }

    /**
     * Get confirmation.
     * @return mixed
     */
    public function getConfirmation()
    {
        $cAttribute = $this->confirmationAttribute;
        return is_string($cAttribute) ? $this->$cAttribute : null;
    }

    /**
     * When confirmation status changed, this event will be triggered. If
     * confirmation succeeded, the confirm_time will be assigned to current time,
     * or the confirm_time will be assigned to initConfirmTime.
     * This method is ONLY used for being triggered by event. DO NOT call,
     * override or modify it directly, unless you know the consequences.
     * @param ModelEvent $event
     */
    public function onConfirmationChanged($event)
    {
        $sender = $event->sender;
        $cAttribute = $sender->confirmationAttribute;
        if (!$cAttribute) {
            return;
        }
        if ($sender->isAttributeChanged($cAttribute)) {
            $sender->confirmCode = '';
            if ($sender->$cAttribute == self::$confirmFalse) {
                $sender->trigger(self::$eventConfirmationCanceled);
                return;
            }
            $sender->trigger(self::$eventConfirmationSuceeded);
            $sender->resetOthersConfirmation();
        }
    }

    /**
     * Get rules associated with confirmation attributes.
     * if not enable confirmation feature, it will return empty array.
     * @return array
     */
    public function getConfirmationRules()
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
    protected function resetConfirmation()
    {
        $contentAttribute = $this->contentAttribute;
        if (!$contentAttribute) {
            return;
        }
        if (is_array($contentAttribute)) {
            foreach ($contentAttribute as $attribute) {
                if ($this->isAttributeChanged($attribute)) {
                    $this->confirmation = self::$confirmFalse;
                    break;
                }
            }
        } elseif ($this->isAttributeChanged($contentAttribute)) {
            $this->confirmation = self::$confirmFalse;
        }
    }

    /**
     * Reset others' confirmation when the others own the same content.
     */
    protected function resetOthersConfirmation()
    {
        if (!$this->confirmationAttribute || empty($this->userClass)) {
            return;
        }
        $contents = self::find()
            ->where([$this->contentAttribute => $this->content])
            ->andWhere(['not', $this->createdByAttribute, $this->creator])
            ->all();
        foreach ($contents as $content) {
            $content->confirmation = self::$confirmFalse;
            $content->save();
        }
    }
}
