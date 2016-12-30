<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link http://vistart.me/
 * @copyright Copyright (c) 2016 vistart
 * @license http://vistart.me/license/
 */

namespace rhosocial\base\models\models;

use rhosocial\base\models\traits\MetaTrait;

/**
 * Description of BaseMetaModel
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
abstract class BaseMetaModel extends BaseBlameableModel
{
    use MetaTrait;

    public $idAttribute = 'key';
    public $idPreassigned = true;

    /**
     * Collation: utf8mb4_unicode_ci
     * MySQL 5.7 supports the length of key more than 767 bytes.
     * @var int 
     */
    public $idAttributeLength = 190;
    public $createdAtAttribute = false;
    public $updatedAtAttribute = false;
    public $enableIP = false;
    public $contentAttribute = 'value';
    public $updatedByAttribute = false;
    public $confirmationAttribute = false;

}
