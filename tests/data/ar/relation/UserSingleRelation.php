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

use rhosocial\base\models\tests\data\ar\User;
use rhosocial\base\models\queries\BaseUserRelationQuery;
use rhosocial\base\models\models\BaseUserRelationModel;

/**
 * @author vistart <i@vistart.me>
 */
class UserSingleRelation extends BaseUserRelationModel
{
    public $multiBlamesAttribute = 'groups';
    public $descriptionAttribute = 'description';
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->userClass = User::class;
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