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
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
abstract class BaseRedisMetaModel extends BaseRedisBlameableModel
{
    use MetaTrait;

    public $idAttribute = 'key';
    public $idPreassigned = true;
    public $idAttributeLength = 255;
    public $createdAtAttribute = false;
    public $updatedAtAttribute = false;
    public $enableIP = false;
    public $contentAttribute = 'value';
    public $updatedByAttribute = false;
    public $confirmationAttribute = false;

}
