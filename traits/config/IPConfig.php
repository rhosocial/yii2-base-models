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

namespace rhosocial\base\models\traits\config;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class IPConfig
{
    const IP_DISABLED = 0x0;
    const IP_V4_ENABLED = 0x1;
    const IP_V6_ENABLED = 0x2;
    const IP_ALL_ENABLED = 0x3;

    public function __construct(
        /**
         * @var int Decide whether to enable IP attributes. Zero means not enabled.
         * All the parameters accepted are listed below.
         */
        public int $enableIP = self::IP_ALL_ENABLED,
        public string $ipAttribute = 'ip',
        public string $ipTypeAttribute = 'ip_type',
    ) {}
}