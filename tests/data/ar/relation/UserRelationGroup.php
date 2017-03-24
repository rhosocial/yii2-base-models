<?php

/**
 *   _   __ __ _____ _____ ___  ____  _____
 *  | | / // // ___//_  _//   ||  __||_   _|
 *  | |/ // /(__  )  / / / /| || |     | |
 *  |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\tests\data\ar\relation;

use rhosocial\base\models\models\BaseUserRelationGroupModel;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class UserRelationGroup extends BaseUserRelationGroupModel
{
    public function __construct($config = array())
    {
        $this->hostClass = User::class;
        parent::__construct($config);
    }
}
