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

use rhosocial\base\models\models\BaseUserRelationModel;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class UserRelation extends BaseUserRelationModel
{
    public string|false $multiBlamesAttribute = 'groups';
    public string|false $descriptionAttribute = 'description';

    public function init()
    {
        $this->multiBlamesClass = UserRelationGroup::class;
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_relation}}';
    }

    /**
     * friendly to IDE;
     * @return \rhosocial\base\models\queries\BaseUserRelationQuery
     */
    public static function find()
    {
        return parent::find();
    }
}
