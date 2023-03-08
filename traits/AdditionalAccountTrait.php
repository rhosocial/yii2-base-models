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

/**
 * Additional account features.
 * This trait should be used in blameable model or its extended class.
 * 
 * When an additional account is enabled with a separate password, this additional
 * account can only log in with its own unique password.
 * Note: The extra account password should not be the same as the master account
 * password, but we will not take the initiative to determine whether the two are the same.
 * 
 * @property bool $separateLogin determines whether this account could be used
 * for logging-in.
 * @property-read array $enableLoginAttributeRules
 * @property-read array $additionAccountRules
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
trait AdditionalAccountTrait
{
    use PasswordTrait;
    
    /**
     * @var false|string The attribute of which determines whether enable to
     * login with current additional account. You can assign it to false if you
     * want to disable this feature, this is equivalent to not allow to log in
     * with current additional account among all the users.
     */
    public string|false $separateLoginAttribute = false;
    
    /**
     * @return bool  Determines whether login with current additional
     * account with a separate password or not. If you set $enableLoginAttribute
     * to false, this feature will be skipped.
     */
    public function getPasswordIsSeparate(): bool
    {
        return $this->getSeparateLogin() && !$this->getIsEmptyPassword();
    }
    
    /**
     * Get this additional account could be used for logging-in.
     * @return bool
     */
    public function getSeparateLogin(): bool
    {
        if (empty($this->separateLoginAttribute)) {
            return false;
        }
        $enableLoginAttribute = $this->separateLoginAttribute;
        return $this->$enableLoginAttribute > 0;
    }
    
    /**
     * Set this additional account could be used for logging-in.
     * @param bool $can
     * @return void
     */
    public function setSeparateLogin(bool $can): void
    {
        if (empty($this->separateLoginAttribute)) {
            return;
        }
        $enableLoginAttribute = $this->separateLoginAttribute;
        $this->$enableLoginAttribute = ($can ? 1 : 0);
    }
    
    /**
     * Get rules associated with enable login attribute.
     * If enable login feature by this additional account, it will return the rules
     * with true by default.
     * @return array rules.
     */
    public function getEnableLoginAttributeRules(): array
    {
        return is_string($this->separateLoginAttribute) && !empty($this->separateLoginAttribute) ? [
            [[$this->separateLoginAttribute], 'boolean'],
            [[$this->separateLoginAttribute], 'default', 'value' => true],
            ] : [];
    }
    
    /**
     * Get rules associated with additional account attributes.
     * @return array rules.
     */
    public function getAdditionalAccountRules(): array
    {
        $rules = $this->getEnableLoginAttributeRules();
        if ($this->getPasswordIsSeparate()) {
            $rules = array_merge($rules, $this->getPasswordHashRules());
        }
        return $rules;
    }
}