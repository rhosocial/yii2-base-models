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

use rhosocial\base\helpers\IP;
use Yii;
use yii\base\ModelEvent;
use yii\web\Request;

/**
 * The IP address features.
 * @property string|null $ipAddress
 * @property integer $ipType
 * @proeperty array $ipRules
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
trait IPTrait
{
    /**
     * @var integer REQUIRED. Determine whether the IP attributes if enabled.
     * All the parameters accepted are listed below.
     */
    public $enableIP = 0x3;
    public static $noIP = 0x0;
    public static $ipv4 = 0x1;
    public static $ipv6 = 0x2;
    public static $ipAll = 0x3;
    
    public $ipAttribute = 'ip';
    public $ipTypeAttribute = 'ip_type';
    public $requestId = 'request';
    
    protected function getWebRequest()
    {
        $requestId = $this->requestId;
        if (!empty($requestId) && is_string($requestId)) {
            $request = Yii::$app->$requestId;
        } else {
            $request = Yii::$app->request;
        }
        if ($request instanceof Request) {
            return $request;
        }
        return null;
    }
    
    protected function attachInitIPEvent($eventName)
    {
        $this->on($eventName, [$this, 'onInitIPAddress']);
    }
    
    /**
     * Initialize IP Attributes.
     * This method is ONLY used for being triggered by event. DO NOT call,
     * override or modify it directly, unless you know the consequences.
     * @param ModelEvent $event
     */
    public function onInitIPAddress($event)
    {
        $sender = $event->sender;
        $request = $sender->getWebRequest();
        if ($sender->enableIP && $request && empty($sender->ipAddress)) {
            $sender->ipAddress = $request->userIP;
        }
    }
    
    /**
     * Get the IPv4 address.
     * @return string
     */
    protected function getIPv4Address()
    {
        return $this->{$this->ipTypeAttribute} == IP::IPv4 ? inet_ntop($this->{$this->ipAttribute}) : null;
    }
    
    /**
     * Get the IPv6 address.
     * @return string
     */
    protected function getIPv6Address()
    {
        return $this->{$this->ipTypeAttribute} == IP::IPv6 ? inet_ntop($this->{$this->ipAttribute}) : null;
    }
    
    /**
     *
     * @param string $ipAddress IPv4 address.
     * @return string
     */
    protected function setIPv4Address($ipAddress)
    {
        return $this->{$this->ipAttribute} = inet_pton($ipAddress);
    }
    
    /**
     *
     * @param string $ipAddress IPv6 address.
     * @return string
     */
    protected function setIPv6Address($ipAddress)
    {
        return $this->{$this->ipAttribute} = inet_pton($ipAddress);
    }
    
    /**
     *
     * @return string
     */
    public function getIPAddress()
    {
        if (!$this->enableIP) {
            return null;
        }
        try {
            if ($this->enableIP == static::$ipv4) {
                return $this->getIPv4Address();
            } elseif ($this->enableIP == static::$ipv6) {
                return $this->getIPv6Address();
            } elseif ($this->enableIP == static::$ipAll) {
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
     * @param string $ipAddress the significantly IP address.
     * @return string|integer|null Integer when succeeded to convert.
     */
    public function setIPAddress($ipAddress)
    {
        if (!$ipAddress || !$this->enableIP) {
            return null;
        }
        $ipType = IP::judgeIPtype($ipAddress);
        if ($ipType == IP::IPv4 && $this->enableIP & static::$ipv4) {
            $this->setIPv4Address($ipAddress);
        } elseif ($ipType == Ip::IPv6 && $this->enableIP & static::$ipv6) {
            $this->setIPv6Address($ipAddress);
        } else {
            return 0;
        }
        if ($this->enableIP & static::$ipAll) {
            $this->{$this->ipTypeAttribute} = $ipType;
        }
        return $ipType;
    }
    
    /**
     * Get the rules associated with ip attributes.
     * @return array
     */
    public function getIPRules()
    {
        $rules = [];
        if ($this->enableIP & static::$ipv4) {
            $rules = [
                [[$this->ipAttribute],
                    'string', 'max' => 4
                ],
            ];
        }
        if ($this->enableIP & static::$ipv6) {
            $rules = [
                [[$this->ipAttribute],
                    'string', 'max' => 16
                ],
            ];
        }
        if ($this->enableIP & static::$ipAll) {
            $rules[] = [
                [$this->ipTypeAttribute], 'in', 'range' => [IP::IPv4, IP::IPv6],
            ];
        }
        return $rules;
    }
    
    /**
     * @inheritdoc
     */
    public function enabledIPFields()
    {
        $fields = [];
        switch ($this->enableIP) {
            case static::$ipAll:
                $fields[] = $this->ipTypeAttribute;
            case static::$ipv6:
            case static::$ipv4:
                $fields[] = $this->ipAttribute;
            case static::$noIP:
            default:
                break;
        }
        return $fields;
    }
}
