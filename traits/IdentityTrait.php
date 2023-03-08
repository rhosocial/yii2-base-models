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
 * User features concerning identity.
 *
 * @property string $accessToken
 * @property array $accessTokenRules
 * @property string $authKey
 * @property array $authKeyRules
 * @property integer $status
 * @property array $statusRules
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
trait IdentityTrait
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    public string|false $statusAttribute = 'status';
    private array $statusRules = [];
    public string|false $authKeyAttribute = 'auth_key';
    private array $authKeyRules = [];
    public string|false $accessTokenAttribute = 'access_token';
    private array $accessTokenRules = [];

    /**
     * Finds an identity by the given ID.
     * @param mixed $identity
     * @return static|null
     */
    public static function findIdentity(mixed $identity): ?static
    {
        $self = static::buildNoInitModel();
        return static::findOne([$self->idAttribute => $identity]);
    }

    /**
     * Finds an identity by the given GUID.
     * @param mixed $guid
     * @return static|null
     */
    public static function findIdentityByGuid(mixed $guid): ?static
    {
        return static::findOne($guid);
    }

    /**
     * Finds an identity by the given token.
     * @param string $token
     * @param mixed $type
     * @return static|null
     */
    public static function findIdentityByAccessToken($token, $type = null): ?static
    {
        $self = static::buildNoInitModel();
        return static::findOne([$self->accessTokenAttribute => $token]);
    }

    /**
     * Get auth key.
     * @return string|null
     */
    public function getAuthKey(): ?string
    {
        $authKeyAttribute = $this->authKeyAttribute;
        return (is_string($authKeyAttribute) && !empty($authKeyAttribute)) ? $this->$authKeyAttribute : null;
    }

    /**
     * Set auth key.
     * @param string $key
     * @return string|null
     */
    public function setAuthKey($key): ?string
    {
        $authKeyAttribute = $this->authKeyAttribute;
        return (is_string($authKeyAttribute) && !empty($authKeyAttribute)) ? $this->$authKeyAttribute = $key : null;
    }

    /**
     * Validate the auth key.
     * @param string $authKey
     * @return bool
     */
    public function validateAuthKey($authKey): bool
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Get the rules associated with auth key attribute.
     * @return array
     */
    public function getAuthKeyRules(): array
    {
        if (!is_string($this->authKeyAttribute) || empty($this->authKeyAttribute)) {
            return [];
        }
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
    public function setAuthKeyRules($rules): void
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
    public function onInitAuthKey($event): void
    {
        $sender = $event->sender;
        /* @var $sender static */
        $sender->setAuthKey(sha1(Yii::$app->security->generateRandomString()));
    }

    /**
     * Get access token.
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        $accessTokenAttribute = $this->accessTokenAttribute;
        return (is_string($accessTokenAttribute) && !empty($accessTokenAttribute)) ? $this->$accessTokenAttribute : null;
    }

    /**
     * Set access token.
     * @param string $token
     * @return string|null
     */
    public function setAccessToken($token): ?string
    {
        $accessTokenAttribute = $this->accessTokenAttribute;
        return (is_string($accessTokenAttribute) && !empty($accessTokenAttribute)) ? $this->$accessTokenAttribute = $token : null;
    }

    /**
     * Get the rules associated with access token attribute.
     * @return array
     */
    public function getAccessTokenRules(): array
    {
        if (!is_string($this->accessTokenAttribute) || empty($this->accessTokenAttribute)) {
            return [];
        }
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
    public function setAccessTokenRules($rules): void
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
     * @throws Exception
     */
    public function onInitAccessToken($event): void
    {
        $sender = $event->sender;
        /* @var $sender static */
        $sender->setAccessToken(sha1(Yii::$app->security->generateRandomString()));
    }

    /**
     * Get status.
     * @return int|null
     */
    public function getStatus(): ?int
    {
        $statusAttribute = $this->statusAttribute;
        return (is_string($statusAttribute) && !empty($statusAttribute)) ? $this->$statusAttribute : null;
    }

    /**
     * Set status.
     * @param integer $status
     * @return int|null
     */
    public function setStatus($status): ?int
    {
        $statusAttribute = $this->statusAttribute;
        return (is_string($statusAttribute) && !empty($statusAttribute)) ? $this->$statusAttribute = $status : null;
    }

    /**
     * Get the rules associated with status attribute.
     * @return array
     */
    public function getStatusRules(): array
    {
        if (!is_string($this->statusAttribute) || empty($this->statusAttribute)) {
            return [];
        }
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
    public function setStatusRules($rules): void
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
    public function onInitStatusAttribute($event): void
    {
        $sender = $event->sender;
        /* @var $sender static */
        if (empty($sender->getStatus())) {
            $sender->setStatus(self::STATUS_ACTIVE);
        }
    }
}
