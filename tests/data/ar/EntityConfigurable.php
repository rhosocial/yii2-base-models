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

use rhosocial\base\models\models\BaseEntityModel;
use rhosocial\base\models\traits\config\EntityConfig;
use rhosocial\base\models\traits\config\GUIDConfig;
use rhosocial\base\models\traits\config\IDConfig;
use rhosocial\base\models\traits\config\IPConfig;
use rhosocial\base\models\traits\config\TimestampConfig;

/**
 * @version 3.0
 * @author vistart <i@vistart.me>
 */
#[EntityConfig(
    new GUIDConfig("guid_conf"),
    new IDConfig("id_conf"),
    new IPConfig(IPConfig::IP_ALL_ENABLED, "ip_conf", "ip_type_conf"),
    new TimestampConfig("created_at_conf", "updated_at_conf"),
)]
class EntityConfigurable extends BaseEntityModel
{
    public static function tableName()
    {
        return '{{%entity}}';
    }
}
