<?php

/**
 *   _   __ __ _____ _____ ___  ____  _____
 *  | | / // // ___//_  _//   ||  __||_   _|
 *  | |/ // /(__  )  / / / /| || |     | |
 *  |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2022 vistart
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
 * @property boolean $seperateLogin determines whether this account could be used
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
     * @var boolean|string The attribute of which determines whether enable to
     * login with current additional account. You can assign it to false if you
     * want to disable this feature, this is equivolent to not allow to login
     * with current additional account among all the users.
     */
    public $seperateLoginAttribute = false;
    
    /**
     * @var boolean  Determines whether login with current additional
     * account with a seperate password or not. If you set $enableLoginAttribute
     * to false, this feature will be skipped.
     */
    public function getPasswordIsSeperate()
    {
        return $this->getSeperateLogin() && !$this->getIsEmptyPassword();
    }
    
    /**
     * Get this additional account could be used for logging-in.
     * @return boolean
     */
    public function getSeperateLogin()
    {
        if (!$this->seperateLoginAttribute || empty($this->seperateLoginAttribute)) {
            return false;
        }
        $enableLoginAttribute = $this->seperateLoginAttribute;
        return $this->$enableLoginAttribute > 0;
    }
    
    /**
     * Set this additional accunt could be used for logging-in.
     * @param boolean $can
     * @return integer
     */
    public function setSeperateLogin($can)
    {
        if (!$this->seperateLoginAttribute || empty($this->seperateLoginAttribute)) {
            return;
        }
        $enableLoginAttribute = $this->seperateLoginAttribute;
        $this->$enableLoginAttribute = ($can ? 1 : 0);
    }
    
    /**
     * Get rules associated with enable login attribute.
     * If enable login feature by this additional account, it will return the rules
     * with true by default.
     * @return array rules.
     */
    public function getEnableLoginAttributeRules()
    {
        return $this->seperateLoginAttribute && is_string($this->seperateLoginAttribute) && !empty($this->seperateLoginAttribute) ? [
            [[$this->seperateLoginAttribute], 'boolean'],
            [[$this->seperateLoginAttribute], 'default', 'value' => true],
            ] : [];
    }
    
    /**
     * Get rules associated with additional account attributes.
     * @return array rules.
     */
    public function getAdditionalAccountRules()
    {
        $rules = $this->getEnableLoginAttributeRules();
        if ($this->getPasswordIsSeperate()) {
            $rules = array_merge($rules, $this->getPasswordHashRules());
        }
        return $rules;
    }
}