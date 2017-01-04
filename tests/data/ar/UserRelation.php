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

namespace rhosocial\base\models\tests\data\ar;

use rhosocial\base\models\models\BaseUserRelationModel;

/**
 * @author vistart <i@vistart.me>
 */
class UserRelation extends BaseUserRelationModel
{
    public $multiBlamesAttribute = 'groups';
    public $descriptionAttribute = 'description';
    
    public function init()
    {
        $this->multiBlamesClass = UserRelationGroup::class;
        parent::init();
    }
    
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