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
readonly class IDConfig
{
    public function __construct(
        public ?string $idAttribute = "id",
        public int $idAttributeType = 0,
        public bool $idPreassigned = false,
        public string $idAttributePrefix = "",
        public int $idAttributeLength = 4,
        public bool $idAttributeSafe = false,
    ) {}
}