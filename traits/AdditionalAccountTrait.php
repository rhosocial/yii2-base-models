<?php

/**
 *   _   __ __ _____ _____ ___  ____  _____
 *  | | / // // ___//_  _//   ||  __||_   _|
 *  | |/ // /(__  )  / / / /| || |     | |
 *  |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\traits;

trait AdditionalAccountTrait
{
    use PasswordTrait;
    
    /**
     * @var boolean|string The attribute of which determines whether enable to
     * login with current additional account. You can assign it to false ff you
     * want to disable this feature, this is equivolent to not allow to login
     * with current additional account among all the users.
     */
    public $enableLoginAttribute = false;
    
    /**
     * @var boolean|string  Determines whether login with current additional
     * account with an independent password or not. If you set $enableLoginAttribute
     * to false, this feature will be skipped.
     */
    public $independentPassword = false;
    
    /**
     * Get this additional account could be used for logging-in.
     * @return boolean
     */
    public function getCanBeLogon()
    {
        if (!$this->enableLoginAttribute) {
            return false;
        }
        $enableLoginAttribute = $this->enableLoginAttribute;
        return $this->$enableLoginAttribute > 0;
    }
    
    /**
     * Set this additional accunt could be used for logging-in.
     * @param boolean $can
     * @return integer
     */
    public function setCanBeLogon($can)
    {
        if (!$this->enableLoginAttribute) {
            return;
        }
        $enableLoginAttribute = $this->enableLoginAttribute;
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
        return $this->enableLoginAttribute && is_string($this->enableLoginAttribute) ? [
            [[$this->enableLoginAttribute], 'boolean'],
            [[$this->enableLoginAttribute], 'default', 'value' => true],
            ] : [];
    }
    
    /**
     * Get rules associated with additional account attributes.
     * @return array rules.
     */
    public function getAdditionalAccountRules()
    {
        $rules = $this->getEnableLoginAttributeRules();
        if ($this->independentPassword) {
            $rules = array_merge($rules, $this->getPasswordHashRules());
        }
        return $rules;
    }
}