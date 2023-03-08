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

namespace rhosocial\base\models\tests\data\ar\relation;

use rhosocial\base\models\tests\data\ar\User;
use rhosocial\base\models\queries\BaseUserRelationQuery;
use rhosocial\base\models\models\BaseUserRelationModel;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class UserSingleRelation extends BaseUserRelationModel
{
    public string|false $multiBlamesAttribute = 'groups';
    public string|false $descriptionAttribute = 'description';

    public function __construct($config = array())
    {
        $this->hostClass = User::class;
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->relationType = static::$relationSingle;
        $this->multiBlamesClass = UserRelationGroup::class;
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_single_relation}}';
    }

    /**
     * Friendly to IDE.
     * @return BaseUserRelationQuery
     */
    public static function find()
    {
        return parent::find();
    }
}
