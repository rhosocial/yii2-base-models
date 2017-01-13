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
 * User features concerning password.
 *
 * Notice! Please DO NOT change password throughout modifying `pass_hash` property,
 * use `setPassword()` magic property instead!
 *
 * Set or directly reset password:
 * ```php
 * $this->password = '<new password>'; // 'afterSetPassword' event will be triggered.
 * $this->save();
 * ```
 *
 * @property-write string $password New password to be set.
 * @property array $passwordHashRules
 * @property array $passwordResetTokenRules
 * @property array $rules
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
trait PasswordTrait
{

    public static $eventAfterSetPassword = "afterSetPassword";
    public static $eventBeforeValidatePassword = "beforeValidatePassword";
    public static $eventValidatePasswordSucceeded = "validatePasswordSucceeded";
    public static $eventValidatePasswordFailed = "validatePasswordFailed";
    public static $eventBeforeResetPassword = "beforeResetPassword";
    public static $eventAfterResetPassword = "afterResetPassword";
    public static $eventResetPasswordFailed = "resetPasswordFailed";
    public static $eventNewPasswordAppliedFor = "newPasswordAppliedFor";
    public static $eventPasswordResetTokenGenerated = "passwordResetTokenGenerated";

    /**
     * @var string The name of attribute used for storing password hash.
     * We strongly recommend you not to change `pass_hash` property directly,
     * please use setPassword() magic property instead.
     */
    public $passwordHashAttribute = 'pass_hash';

    /**
     * @var string The name of attribute used for storing password reset token.
     * If you do not want to provide password reset feature, please set `false`.
     */
    public $passwordResetTokenAttribute = 'password_reset_token';

    /**
     * @var integer Cost parameter used by the Blowfish hash algorithm.
     */
    public $passwordCost = 13;

    /**
     * @var integer if $passwordHashStrategy equals 'crypt', this value statically
     * equals 60.
     */
    public $passwordHashAttributeLength = 60;
    private $passwordHashRules = [];
    private $passwordResetTokenRules = [];
    
    /**
     * Return the empty password specialty.
     * NOTE: PLEASE OVERRIDE THIS METHOD TO SPECIFY YOUR OWN EMPTY PASSWORD SPECIALTY.
     * - The length of specialty should be greater than 18.
     * - Uppercase and lowercase letters, punctuation marks, numbers, and underscores are required.
     * @return string The string regarded as empty password.
     */
    protected function getEmptyPasswordSpecialty()
    {
        return 'Rrvl-7}cXt_<iAx[5s';
    }

    /**
     * Get rules of password hash.
     * @return array password hash rules.
     */
    public function getPasswordHashRules()
    {
        if (empty($this->passwordHashRules) || !is_array($this->passwordHashRules)) {
            $this->passwordHashRules = [
                [[$this->passwordHashAttribute], 'string', 'max' => $this->passwordHashAttributeLength],
            ];
        }
        return $this->passwordHashRules;
    }

    /**
     * Set rules of password hash.
     * @param array $rules password hash rules.
     */
    public function setPasswordHashRules($rules)
    {
        if (!empty($rules) && is_array($rules)) {
            $this->passwordHashRules = $rules;
        }
    }

    /**
     * Get the rules associated with password reset token attribute.
     * If password reset feature is not enabled, the empty array will be given.
     * @return mixed
     */
    public function getPasswordResetTokenRules()
    {
        if (!is_string($this->passwordResetTokenAttribute)) {
            return [];
        }
        if (empty($this->passwordResetTokenRules) || !is_array($this->passwordResetTokenRules)) {
            $this->passwordResetTokenRules = [
                [[$this->passwordResetTokenAttribute], 'string', 'length' => 40],
                [[$this->passwordResetTokenAttribute], 'unique'],
            ];
        }
        return $this->passwordResetTokenRules;
    }

    /**
     * Set the rules associated with password reset token attribute.
     * @param mixed $rules
     */
    public function setPasswordResetTokenRules($rules)
    {
        if (!empty($rules) && is_array($rules)) {
            $this->passwordResetTokenRules = $rules;
        }
    }

    /**
     * Generates a secure hash from a password and a random salt.
     *
     * The generated hash can be stored in database.
     * Later when a password needs to be validated, the hash can be fetched and passed
     * to [[validatePassword()]]. For example,
     *
     * ~~~
     * // generates the hash (usually done during user registration or when the password is changed)
     * $hash = Yii::$app->getSecurity()->generatePasswordHash($password);
     * // ...save $hash in database...
     *
     * // during login, validate if the password entered is correct using $hash fetched from database
     * if (Yii::$app->getSecurity()->validatePassword($password, $hash) {
     *     // password is good
     * } else {
     *     // password is bad
     * }
     * ~~~
     *
     * @param string $password The password to be hashed.
     * @return string The password hash string. When [[passwordHashStrategy]] is set to 'crypt',
     * the output is always 60 ASCII characters, when set to 'password_hash' the output length
     * might increase in future versions of PHP (http://php.net/manual/en/function.password-hash.php)
     */
    public function generatePasswordHash($password)
    {
        return Yii::$app->security->generatePasswordHash((string)$password, $this->passwordCost);
    }

    /**
     * Verifies a password against a hash.
     * @param string $password The password to verify.
     * @return boolean whether the password is correct.
     */
    public function validatePassword($password)
    {
        $phAttribute = $this->passwordHashAttribute;
        $result = Yii::$app->security->validatePassword($password, $this->$phAttribute);
        if ($result) {
            $this->trigger(static::$eventValidatePasswordSucceeded);
            return $result;
        }
        $this->trigger(static::$eventValidatePasswordFailed);
        return $result;
    }

    /**
     * Set new password.
     * @param string $password the new password to be set.
     */
    public function setPassword($password = null)
    {
        if (empty($password)) {
            $password = $this->getEmptyPasswordSpecialty();
        }
        $phAttribute = $this->passwordHashAttribute;
        $this->$phAttribute = $this->generatePasswordHash($password);
        $this->trigger(static::$eventAfterSetPassword);
    }
    
    /**
     * Set empty password.
     */
    public function setEmptyPassword()
    {
        $this->password = $this->getEmptyPasswordSpecialty();
    }
    
    /**
     * Check whether password is empty.
     * @return boolean
     */
    public function getIsEmptyPassword()
    {
        return 
        (!is_string($this->passwordHashAttribute) || empty($this->passwordHashAttribute)) ? 
        true : $this->validatePassword($this->getEmptyPasswordSpecialty());
    }

    /**
     * Apply for new password.
     * If this model is new one, false will be given, and no events will be triggered.
     * If password reset feature is not enabled, `$eventNewPasswordAppliedFor`
     * will be triggered and return true directly.
     * Otherwise, the new password reset token will be regenerated and saved. Then
     * trigger the `$eventNewPasswordAppliedFor` and
     * `$eventPasswordResetTokenGenerated` events and return true.
     * @return boolean
     */
    public function applyForNewPassword()
    {
        if ($this->isNewRecord) {
            return false;
        }
        if (!is_string($this->passwordResetTokenAttribute)) {
            $this->trigger(static::$eventNewPasswordAppliedFor);
            return true;
        }
        $prtAttribute = $this->passwordResetTokenAttribute;
        $this->$prtAttribute = static::generatePasswordResetToken();
        if (!$this->save()) {
            $this->trigger(static::$eventResetPasswordFailed);
            return false;
        }
        $this->trigger(static::$eventNewPasswordAppliedFor);
        $this->trigger(static::$eventPasswordResetTokenGenerated);
        return true;
    }

    /**
     * Reset password with password reset token.
     * It will validate password reset token, before reseting password.
     * @param string $password New password to be reset.
     * @param string $token Password reset token.
     * @return boolean whether reset password successfully or not.
     */
    public function resetPassword($password, $token)
    {
        if (!$this->validatePasswordResetToken($token)) {
            return false;
        }
        $this->trigger(static::$eventBeforeResetPassword);
        $this->password = $password;
        if (is_string($this->passwordResetTokenAttribute)) {
            $this->setPasswordResetToken();
        }
        if (!$this->save()) {
            $this->trigger(static::$eventResetPasswordFailed);
            return false;
        }
        $this->trigger(static::$eventAfterResetPassword);
        return true;
    }

    /**
     * Generate password reset token.
     * @return string
     */
    public static function generatePasswordResetToken()
    {
        return sha1(Yii::$app->security->generateRandomString());
    }

    /**
     * The event triggered after new password set.
     * The auth key and access token should be regenerated if new password has applied.
     * @param ModelEvent $event
     */
    public function onAfterSetNewPassword($event)
    {
        $this->onInitAuthKey($event);
        $this->onInitAccessToken($event);
    }

    /**
     * Validate whether the $token is the valid password reset token.
     * If password reset feature is not enabled, true will be given.
     * @param string $token
     * @return boolean whether the token is correct.
     */
    protected function validatePasswordResetToken($token)
    {
        if (!is_string($this->passwordResetTokenAttribute)) {
            return true;
        }
        return $this->getPasswordResetToken() === $token;
    }

    /**
     * Initialize password reset token attribute.
     * @param ModelEvent $event
     */
    public function onInitPasswordResetToken($event)
    {
        $sender = $event->sender;
        if (!is_string($sender->passwordResetTokenAttribute)) {
            return;
        }
        $this->setPasswordResetToken();
    }
    
    /**
     * Set password reset token.
     * @param string $token
     * @return string
     */
    public function setPasswordResetToken($token = '')
    {
        return $this->{$this->passwordResetTokenAttribute} = $token;
    }
    
    /**
     * Get password reset token.
     * @return string
     */
    public function getPasswordResetToken()
    {
        return $this->{$this->passwordResetTokenAttribute};
    }
}
