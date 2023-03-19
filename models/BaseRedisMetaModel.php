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

namespace rhosocial\base\models\models;

use rhosocial\base\models\traits\MetaTrait;

/**
 * Description of BaseRedisMetaModel
 *
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
abstract class BaseRedisMetaModel extends BaseRedisBlameableModel
{
    use MetaTrait;

    public string|false $idAttribute = 'key';
    public bool $idPreassigned = true;
    public int $idAttributeLength = 255;
    public string|false $createdAtAttribute = false;
    public string|false $updatedAtAttribute = false;
    public int $enableIP = 0;
    public string|array|false $contentAttribute = 'value';
    public string|false $updatedByAttribute = false;
    public string|false $confirmationAttribute = false;

}
