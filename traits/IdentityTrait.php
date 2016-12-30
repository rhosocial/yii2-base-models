<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link http://vistart.me/
 * @copyright Copyright (c) 2016 vistart
 * @license http://vistart.me/license/
 */

namespace rhosocial\base\models\traits;

use Yii;
use yii\base\ModelEvent;

/**
 * User features concerning identity.
 *
 * @property-read string $authKey
 * @property array $statusRules
 * @property array $authKeyRules
 * @property array $accessTokenRules
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
trait IdentityTrait
{

    public static $statusActive = 1;
    public static $statusInactive = 0;
    public $statusAttribute = 'status';
    private $statusRules = [];
    public $authKeyAttribute = 'auth_key';
    private $authKeyRules = [];
    public $accessTokenAttribute = 'access_token';
    private $accessTokenRules = [];

    /**
     * Finds an identity by the given ID.
     * @param string|integer $identity
     * @return type
     */
    public static function findIdentity($identity)
    {
        $self = static::buildNoInitModel();
        return static::findOne([$self->idAttribute => $identity]);
    }

    /**
     * Finds an identity by the given GUID.
     * @param string $guid
     * @return type
     */
    public static function findIdentityByGuid($guid)
    {
        return static::findOne($guid);
    }

    /**
     * Finds an identity by the given token.
     * @param string $token
     * @param type $type
     * @return type
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $self = static::buildNoInitModel();
        return static::findOne([$self->accessTokenAttribute => $token]);
    }

    /**
     * Get auth key.
     * @return string|null
     */
    public function getAuthKey()
    {
        $authKeyAttribute = $this->authKeyAttribute;
        return is_string($authKeyAttribute) ? $this->$authKeyAttribute : null;
    }

    /**
     * Set auth key.
     * @param string $key
     * @return string
     */
    public function setAuthKey($key)
    {
        $authKeyAttribute = $this->authKeyAttribute;
        return is_string($authKeyAttribute) ? $this->$authKeyAttribute = $key : null;
    }

    /**
     * Validate the auth key.
     * @param string $authKey
     * @return string
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Get the rules associated with auth key attribute.
     * @return array
     */
    public function getAuthKeyRules()
    {
        if (empty($this->authKeyRules)) {
            $this->authKeyRules = [
                [[$this->authKeyAttribute], 'required'],
                [[$this->authKeyAttribute], 'string', 'max' => 40],
            ];
        }
        return $this->authKeyRules;
    }

    /**
     * Set the rules associated with auth key attribute.
     * @param array $rules
     */
    public function setAuthKeyRules($rules)
    {
        if (!empty($rules) && is_array($rules)) {
            $this->authKeyRules = $rules;
        }
    }

    /**
     * Initialize the auth key attribute.
     * This method is ONLY used for being triggered by event. DO NOT call,
     * override or modify it directly, unless you know the consequences.
     * @param ModelEvent $event
     */
    public function onInitAuthKey($event)
    {
        $sender = $event->sender;
        $authKeyAttribute = $sender->authKeyAttribute;
        $sender->$authKeyAttribute = sha1(Yii::$app->security->generateRandomString());
    }

    /**
     * Get access token.
     * @return string|null
     */
    public function getAccessToken()
    {
        $accessTokenAttribute = $this->accessTokenAttribute;
        return is_string($accessTokenAttribute) ? $this->$accessTokenAttribute : null;
    }

    /**
     * Set access token.
     * @param string $token
     * @return string|null
     */
    public function setAccessToken($token)
    {
        $accessTokenAttribute = $this->accessTokenAttribute;
        return is_string($accessTokenAttribute) ? $this->$accessTokenAttribute = $token : null;
    }

    /**
     * Get the rules associated with access token attribute.
     * @return array
     */
    public function getAccessTokenRules()
    {
        if (empty($this->accessTokenRules)) {
            $this->accessTokenRules = [
                [[$this->accessTokenAttribute], 'required'],
                [[$this->accessTokenAttribute], 'string', 'max' => 40],
            ];
        }
        return $this->accessTokenRules;
    }

    /**
     * Set the rules associated with access token attribute.
     * @param array $rules
     */
    public function setAccessTokenRules($rules)
    {
        if (!empty($rules) && is_array($rules)) {
            $this->accessTokenRules = $rules;
        }
    }

    /**
     * Initialize the access token attribute.
     * This method is ONLY used for being triggered by event. DO NOT call,
     * override or modify it directly, unless you know the consequences.
     * @param ModelEvent $event
     */
    public function onInitAccessToken($event)
    {
        $sender = $event->sender;
        $accessTokenAttribute = $sender->accessTokenAttribute;
        $sender->$accessTokenAttribute = sha1(Yii::$app->security->generateRandomString());
    }

    /**
     * Get status.
     * @return integer
     */
    public function getStatus()
    {
        $statusAttribute = $this->statusAttribute;
        return is_string($statusAttribute) ? $this->$statusAttribute : null;
    }

    /**
     * Set status.
     * @param integer $status
     * @return integer|null
     */
    public function setStatus($status)
    {
        $statusAttribute = $this->statusAttribute;
        return is_string($statusAttribute) ? $this->$statusAttribute = $status : null;
    }

    /**
     * Get the rules associated with status attribute.
     * @return array
     */
    public function getStatusRules()
    {
        if (empty($this->statusRules)) {
            $this->statusRules = [
                [[$this->statusAttribute], 'required'],
                [[$this->statusAttribute], 'number', 'integerOnly' => true, 'min' => 0],
            ];
        }
        return $this->statusRules;
    }

    /**
     * Set the rules associated with status attribute.
     * @param array $rules
     */
    public function setStatusRules($rules)
    {
        if (!empty($rules) && is_array($rules)) {
            $this->statusRules = $rules;
        }
    }

    /**
     * Initialize the status attribute.
     * This method is ONLY used for being triggered by event. DO NOT call,
     * override or modify it directly, unless you know the consequences.
     * @param ModelEvent $event
     */
    public function onInitStatusAttribute($event)
    {
        $sender = $event->sender;
        $statusAttribute = $sender->statusAttribute;
        if (empty($sender->$statusAttribute)) {
            $sender->$statusAttribute = self::$statusActive;
        }
    }
}
