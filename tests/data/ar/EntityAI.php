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

namespace rhosocial\base\models\tests\data\ar;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class EntityAI extends Entity
{
    public int $idAttributeType = 2; // Auto Increment.
    
    public static function tableName(): string
    {
        return '{{%entity_ai}}';
    }
}
