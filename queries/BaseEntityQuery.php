<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2017 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\queries;

use rhosocial\base\models\traits\EntityQueryTrait;

/**
 * Description of BaseEntityQuery
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class BaseEntityQuery extends \yii\db\ActiveQuery
{
    use EntityQueryTrait;

    public function init()
    {
        $this->buildNoInitModel();
        parent::init();
    }
}
