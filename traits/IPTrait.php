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

use rhosocial\base\helpers\IP;
use Yii;
use yii\web\Request;

/**
 * This trait will handle the IP address attribute of entity.
 * It supports simultaneous processing of IPv4 and IPv6 addresses.
 *
 * Considering that the lengths of IPv4 addresses and IPv6 addresses are different, in order to support both at the same
 * time, this trait uses 128-bit binary attribute to save the original value of the IP address. Therefore, if you want
 * to use IPv6 addresses, the database schema of attribute should be binary with a length of at least 128 bits. If only
 * IPv4 addresses are used, the length can be up to 32 bits.
 *
 * @property string|null $ipAddress
 * @property int $ipType
 * @property  array $ipRules
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
trait IPTrait
{
    const IP_DISABLED = 0x0;
    const IP_V4_ENABLED = 0x1;
    const IP_V6_ENABLED = 0x2;
    const IP_ALL_ENABLED = 0x3;

    /**
     * @var int Decide whether to enable IP attributes. Zero means not enabled.
     * All the parameters accepted are listed below.
     */
    public int $enableIP = self::IP_ALL_ENABLED;
    
    public string $ipAttribute = 'ip';
    public string $ipTypeAttribute = 'ip_type';
    public string|Request|null $requestId = 'request';
    
    protected function getWebRequest(): ?Request
    {
        $requestId = $this->requestId;
        if (!empty($requestId)) {
            $request = Yii::$app->$requestId;
        } else {
            $request = Yii::$app->request;
        }
        if ($request instanceof Request) {
            return $request;
        }
        return null;
    }
    
    protected function attachInitIPEvent($eventName): void
    {
        $this->on($eventName, [$this, 'onInitIPAddress']);
    }
    
    /**
     * Initialize IP Attributes.
     * This method is ONLY used for being triggered by event. DO NOT call,
     * override or modify it directly, unless you know the consequences.
     * @param $event
     */
    public function onInitIPAddress($event): void
    {
        $sender = $event->sender;
        /* @var $sender static */
        $request = $sender->getWebRequest();
        if ($sender->enableIP > 0 && $sender->enableIP <= 3 && $request && empty($sender->ipAddress)) {
            $sender->setIPAddress($request->userIP);
        }
    }

    /**
     * Get the IPv4 address.
     * @return string|null
     */
    protected function getIPv4Address(): ?string
    {
        return $this->{$this->ipTypeAttribute} == IP::IPv4 ? inet_ntop($this->{$this->ipAttribute}) : null;
    }

    /**
     * Get the IPv6 address.
     * @return string|null
     */
    protected function getIPv6Address(): ?string
    {
        return $this->{$this->ipTypeAttribute} == IP::IPv6 ? inet_ntop($this->{$this->ipAttribute}) : null;
    }
    
    /**
     *
     * @param string $ipAddress IPv4 address.
     * @return string
     */
    protected function setIPv4Address(string $ipAddress): string
    {
        return $this->{$this->ipAttribute} = inet_pton($ipAddress);
    }
    
    /**
     *
     * @param string $ipAddress IPv6 address.
     * @return string
     */
    protected function setIPv6Address(string $ipAddress): string
    {
        return $this->{$this->ipAttribute} = inet_pton($ipAddress);
    }
    
    /**
     * Get IP Address.
     * @return ?string
     */
    public function getIPAddress(): ?string
    {
        if ($this->enableIP <= 0 || $this->enableIP > 3) {
            return null;
        }
        try {
            if ($this->enableIP == self::IP_V4_ENABLED) {
                return $this->getIPv4Address();
            } elseif ($this->enableIP == self::IP_V6_ENABLED) {
                return $this->getIPv6Address();
            } elseif ($this->enableIP == self::IP_ALL_ENABLED) {
                if ($this->{$this->ipTypeAttribute} == IP::IPv4) {
                    return $this->getIPv4Address();
                }
                if ($this->{$this->ipTypeAttribute} == IP::IPv6) {
                    return $this->getIPv6Address();
                }
            }
        } catch (\Exception $ex) {
            Yii::error($ex->getMessage(), __METHOD__);
        }
        return null;
    }

    /**
     * Convert the IP address to integer, and store it(them) to ipAttribute*.
     * If you disable($this->enableIP = false) the IP feature, this method will
     * be skipped(return null).
     * @param string|null $ipAddress the readable IP address.
     * @return string|integer|null Integer when succeeded to convert.
     */
    public function setIPAddress(?string $ipAddress): int|string|null
    {
        if (!$ipAddress || !$this->enableIP) {
            return null;
        }
        $ipType = IP::judgeIPtype($ipAddress);
        if ($ipType == IP::IPv4 && $this->enableIP & self::IP_V4_ENABLED) {
            $this->setIPv4Address($ipAddress);
        } elseif ($ipType == IP::IPv6 && $this->enableIP & self::IP_V6_ENABLED) {
            $this->setIPv6Address($ipAddress);
        } else {
            return 0;
        }
        if ($this->enableIP & self::IP_ALL_ENABLED) {
            $this->{$this->ipTypeAttribute} = $ipType;
        }
        return $ipType;
    }
    
    /**
     * Get the rules associated with ip attributes.
     * @return array
     */
    public function getIPRules(): array
    {
        $rules = [];
        if ($this->enableIP & self::IP_V4_ENABLED) {
            $rules = [
                [[$this->ipAttribute],
                    'string', 'max' => 4
                ],
            ];
        }
        if ($this->enableIP & self::IP_V6_ENABLED) {
            $rules = [
                [[$this->ipAttribute],
                    'string', 'max' => 16
                ],
            ];
        }
        if ($this->enableIP & self::IP_ALL_ENABLED) {
            $rules[] = [
                [$this->ipTypeAttribute], 'in', 'range' => [IP::IPv4, IP::IPv6],
            ];
        }
        return $rules;
    }
    
    /**
     * @inheritdoc
     */
    public function enabledIPFields(): array
    {
        $fields = [];
        switch ($this->enableIP) {
            case self::IP_ALL_ENABLED:
                $fields[] = $this->ipTypeAttribute;
            case self::IP_V6_ENABLED:
            case self::IP_V4_ENABLED:
                $fields[] = $this->ipAttribute;
            case self::IP_DISABLED:
            default:
                break;
        }
        return $fields;
    }
}
