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

namespace rhosocial\base\models\web;

use rhosocial\base\models\models\BaseUserModel;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class User extends \yii\web\User
{
    private $_access = [];

    /**
     * Get the GUID of identity.
     * If no user logged-in, null will be given.
     * @return null|string
     */
    public function getGuid()
    {
        $identity = $this->getIdentity();
        /* @var $identity BaseUserModel */
        return $identity !== null ? $identity->getGUID() : null;
    }

    /**
     * @param string $permissionName
     * @param array $params
     * @param bool $allowCaching
     * @return bool|mixed
     */
    public function can($permissionName, $params = [], $allowCaching = true)
    {
        if ($allowCaching && empty($params) && isset($this->_access[$permissionName])) {
            return $this->_access[$permissionName];
        }
        if (($accessChecker = $this->getAccessChecker()) === null) {
            return false;
        }
        $access = $accessChecker->checkAccess($this->getGuid(), $permissionName, $params);
        if ($allowCaching && empty($params)) {
            $this->_access[$permissionName] = $access;
        }

        return $access;
    }
}
