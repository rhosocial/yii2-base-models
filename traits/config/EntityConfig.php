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
readonly class EntityConfig
{
    public function __construct(
        public GUIDConfig $GUIDConfig = new GUIDConfig(),
        public IDConfig $IDConfig = new IDConfig(),
        public IPConfig $IPConfig = new IPConfig(),
        public TimestampConfig $timestampConfig = new TimestampConfig(),
    ) {}
}