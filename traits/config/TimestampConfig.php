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
readonly class TimestampConfig
{
    public function __construct(

        /**
         * @var string|false Specifies the attribute name that records the creation time.
         * Set this attribute to false if you do not want to record it and know whether the entity has been edited.
         */
        public ?string $createdAtAttribute = 'created_at',

        /**
         * @var string|false Specifies the attribute name that records the last update time.
         * Set this attribute to false if you do not want to record it and know whether the entity has been edited.
         */
        public ?string $updatedAtAttribute = 'updated_at',

        /**
         * @var string|false This attribute determines when the current entity expires.
         * If not set, this function will not be enabled.
         */
        public ?string $expiredAfterAttribute = null,

        /**
         * @var int Determine the format of timestamp.
         */
        public int $timeFormat = 0,

        /**
         * @var int Determine the type of timestamp.
         */
        public int $timeType = 0,
    ){}
}