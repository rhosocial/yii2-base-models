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
 * @property string|null $passwordResetToken Password Reset Token.
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
trait PasswordTrait
{
    const EVENT_AFTER_SET_PASSWORD = 'afterSetPassword';
    const EVENT_BEFORE_VALIDATE_PASSWORD = 'beforeValidatePassword';
    const EVENT_VALIDATE_PASSWORD_SUCCEEDED = 'validatePasswordSucceeded';
    const EVENT_VALIDATE_PASSWORD_FAILED = 'validatePasswordFailed';
    const EVENT_BEFORE_RESET_PASSWORD = 'beforeResetPassword';
    const EVENT_AFTER_RESET_PASSWORD = 'afterResetPassword';
    const EVENT_RESET_PASSWORD_FAILED = 'resetPasswordFailed';
    const EVENT_NEW_PASSWORD_APPLIED_FOR = 'newPasswordAppliedFor';
    const EVENT_PASSWORD_RESET_TOKEN_GENERATED = 'passwordResetTokenGenerated';

    /**
     * @var string|false The name of attribute used for storing password hash.
     * We strongly recommend you not to change `pass_hash` property directly,
     * please use setPassword() magic property instead.
     */
    public string|false $passwordHashAttribute = 'pass_hash';

    /**
     * @var string|false The name of attribute used for storing password reset token.
     * Please ensure that this field is unique in the database while allowing null values.
     * If you do not want to provide password reset feature, please set `false`.
     */
    public string|false $passwordResetTokenAttribute = 'password_reset_token';

    /**
     * @var int Cost parameter used by the Blowfish hash algorithm.
     */
    public int $passwordCost = 13;

    /**
     * @var int if $passwordHashStrategy equals 'crypt', this value statically
     * equals 60.
     */
    public int $passwordHashAttributeLength = 60;
    private array $passwordHashRules = [];
    private array $passwordResetTokenRules = [];
    
    /**
     * Return the empty password specialty.
     * NOTE: PLEASE OVERRIDE THIS METHOD TO SPECIFY YOUR OWN EMPTY PASSWORD SPECIALTY.
     * - The length of specialty should be greater than 18.
     * - Uppercase and lowercase letters, punctuation marks, numbers, and underscores are required.
     * @return string The string regarded as empty password.
     */
    protected function getEmptyPasswordSpecialty(): string
    {
        return 'Rrvl-7}cXt_<iAx[5s';
    }

    /**
     * @var string Temporarily store the original password.
     * Note that the content cannot be exposed to the outside, otherwise the password will be leaked.
     */
    protected string $_password;

    /**
     * Get rules of password hash.
     * @return array password hash rules.
     */
    public function getPasswordHashRules(): array
    {
        if (!is_string($this->passwordHashAttribute) || empty($this->passwordHashAttribute)) {
            return [];
        }
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
    public function setPasswordHashRules(array $rules): void
    {
        if (!empty($rules)) {
            $this->passwordHashRules = $rules;
        }
    }

    /**
     * Get the rules associated with password reset token attribute.
     * If password reset feature is not enabled, the empty array will be given.
     * @return array
     */
    public function getPasswordResetTokenRules(): array
    {
        if (!is_string($this->passwordResetTokenAttribute) || empty($this->passwordResetTokenAttribute)) {
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
    public function setPasswordResetTokenRules(mixed $rules): void
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
     * @throws Exception
     */
    public function generatePasswordHash(string $password): string
    {
        return Yii::$app->security->generatePasswordHash((string)$password, $this->passwordCost);
    }

    /**
     * Verifies a password against a hash.
     * @param string $password The password to verify.
     * @return boolean whether the password is correct.
     */
    public function validatePassword(string $password): bool
    {
        $phAttribute = $this->passwordHashAttribute;
        $result = Yii::$app->security->validatePassword($password, $this->$phAttribute);
        if ($result) {
            $this->trigger(static::EVENT_VALIDATE_PASSWORD_SUCCEEDED);
            return $result;
        }
        $this->trigger(static::EVENT_VALIDATE_PASSWORD_FAILED);
        return $result;
    }

    /**
     * Set new password.
     * If $password is empty, the specialty which represents the empty will be taken.
     * Finally, it will trigger `static::$eventAfterSetPassword` event.
     * @param string|null $password the new password to be set.
     * @throws Exception
     */
    public function setPassword(?string $password = null): void
    {
        if (empty($password)) {
            $password = $this->getEmptyPasswordSpecialty();
        }
        $phAttribute = $this->passwordHashAttribute;
        if (empty($phAttribute) || !is_string($phAttribute)) {
            return;
        }
        $this->$phAttribute = $this->generatePasswordHash($password);
        $this->_password = $password;
        $this->trigger(static::EVENT_AFTER_SET_PASSWORD);
    }
    
    /**
     * Set empty password.
     */
    public function setEmptyPassword(): void
    {
        $this->password = $this->getEmptyPasswordSpecialty();
    }
    
    /**
     * Check whether password is empty.
     * @return bool
     */
    public function getIsEmptyPassword(): bool
    {
        return
            !is_string($this->passwordHashAttribute) || empty($this->passwordHashAttribute) || $this->validatePassword($this->getEmptyPasswordSpecialty());
    }

    /**
     * Apply for new password.
     * If this model is new one, false will be given, and no events will be triggered.
     * If password reset feature is not enabled, `$eventNewPasswordAppliedFor`
     * will be triggered and return true directly.
     * Otherwise, the new password reset token will be regenerated and saved. Then
     * trigger the `$eventNewPasswordAppliedFor` and
     * `$eventPasswordResetTokenGenerated` events and return true.
     * @return bool
     */
    public function applyForNewPassword(): bool
    {
        if ($this->isNewRecord) {
            return false;
        }
        if (!is_string($this->passwordResetTokenAttribute)) {
            $this->trigger(static::EVENT_NEW_PASSWORD_APPLIED_FOR);
            return true;
        }
        $this->setPasswordResetToken(static::generatePasswordResetToken());
        if (!$this->save()) {
            $this->trigger(static::EVENT_RESET_PASSWORD_FAILED);
            return false;
        }
        $this->trigger(static::EVENT_NEW_PASSWORD_APPLIED_FOR);
        $this->trigger(static::EVENT_PASSWORD_RESET_TOKEN_GENERATED);
        return true;
    }

    /**
     * Reset password with password reset token.
     * It will validate password reset token, before resetting password.
     * @param string $password New password to be reset.
     * @param string $token Password reset token.
     * @return bool whether reset password successfully or not.
     */
    public function resetPassword(string $password, string $token): bool
    {
        if (!$this->validatePasswordResetToken($token)) {
            return false;
        }
        $this->trigger(static::EVENT_BEFORE_RESET_PASSWORD);
        $this->password = $password;
        $this->setPasswordResetToken();
        if (!$this->save()) {
            $this->trigger(static::EVENT_RESET_PASSWORD_FAILED);
            return false;
        }
        $this->trigger(static::EVENT_AFTER_RESET_PASSWORD);
        return true;
    }

    /**
     * Generate password reset token.
     * The token is hash value of `sha1()` pass through the random string.
     * @return string The generated password reset token.
     */
    public static function generatePasswordResetToken(): string
    {
        return sha1(Yii::$app->security->generateRandomString());
    }

    /**
     * The event triggered after new password set.
     * The auth key and access token should be regenerated if new password has applied.
     * @param ModelEvent $event
     */
    public function onAfterSetNewPassword($event): void
    {
        $this->onInitAuthKey($event);
        $this->onInitAccessToken($event);
    }

    /**
     * Validate whether the `$token` is the valid password reset token.
     * If password reset feature is not enabled, true will be given.
     * Note: We DO NOT treat the `null` specially.
     * @param string|null $token the token to be validated.
     * @return bool whether the $token is correct.
     */
    protected function validatePasswordResetToken(?string $token): bool
    {
        if (!is_string($this->passwordResetTokenAttribute) || empty($this->passwordResetTokenAttribute)) {
            return true;
        }
        return $this->getPasswordResetToken() === $token;
    }

    /**
     * Initialize password reset token attribute.
     * The password reset token attribute would be set `null`.
     * Please ensure that this field is unique in the database while allowing null values.
     * @param ModelEvent $event
     */
    public function onInitPasswordResetToken($event): ?string
    {
        $sender = $event->sender;
        /* @var $sender static */
        return $sender->setPasswordResetToken(null);
    }

    /**
     * Set password reset token.
     * @param string|null $token
     * @return string|null
     */
    public function setPasswordResetToken(?string $token = null): ?string
    {
        if (empty($this->passwordResetTokenAttribute) || !is_string($this->passwordResetTokenAttribute)) {
            return null;
        }
        return $this->{$this->passwordResetTokenAttribute} = $token;
    }
    
    /**
     * Get password reset token.
     * @return string|null Null if this attribute is not enabled, or the value is `null`.
     */
    public function getPasswordResetToken(): ?string
    {
        if (empty($this->passwordResetTokenAttribute) || !is_string($this->passwordResetTokenAttribute)) {
            return null;
        }
        return $this->{$this->passwordResetTokenAttribute};
    }
}
