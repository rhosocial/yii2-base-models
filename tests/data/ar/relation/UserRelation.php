<?php

/**
 *   _   __ __ _____ _____ ___  ____  _____
 *  | | / // // ___//_  _//   ||  __||_   _|
 *  | |/ // /(__  )  / / / /| || |     | |
 *  |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2017 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\tests\data\ar\relation;

use rhosocial\base\models\models\BaseUserRelationModel;
use rhosocial\base\models\tests\data\ar\User;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class UserRelation extends BaseUserRelationModel
{
    public $multiBlamesAttribute = 'groups';
    public $descriptionAttribute = 'description';

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
     * Friendly to IDE.
     * @return BaseUserRelationQuery
     */
    public static function find()
    {
        return parent::find();
    }
}
