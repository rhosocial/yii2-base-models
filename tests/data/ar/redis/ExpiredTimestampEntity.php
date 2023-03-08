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

namespace rhosocial\base\models\tests\data\ar\redis;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class ExpiredTimestampEntity extends TimestampEntity
{
    public string|false $expiredAfterAttribute = 'expired_after';
}
