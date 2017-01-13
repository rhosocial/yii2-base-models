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

namespace rhosocial\base\models\models;

use rhosocial\base\models\queries\BaseUserRelationQuery;
use rhosocial\base\models\traits\UserRelationTrait;

/**
 * This abstract class helps you build user relation model.
 * If you want to use group feature, the BaseUserRelationGroup's extended class
 * must be used coordinately.
 * Basic usage:
 * - Implementation:
 * ~~~php
 * class SingleRelation extends BaseUserRelationModel {
 *     public $relationType = 0; // 0 represents single relation, 1 represents mutual relation. 1 is default.
 *     public $multiBlamesAttribute = 'groups'; // you should redeclare this property if needed. 'blames' is default.
 *
 *     public function init()
 *     {
 *         $this->multiBlamesClass = UserRelationGroup::class; // if you need relation group feature, `$multiBlamesClass` property should be specified.
 *         parent::init(); // parent's call should be placed at the end of init() method.
 *     }
 * }
 * ~~~
 * or:
 * ~~~php
 * class Relation extends BaseUserRelationModel {
 *     public $multiBlamesAttribute = 'groups'; // you should redeclare this property if needed. 'blames' is default.
 *
 *     public function init()
 *     {
 *         $this->multiBlamesClass = UserRelationGroup::class; // if you need relation group feature, `$multiBlamesClass` property should be specified.
 *         parent::init(); // parent's call should be placed at the end of init() method.
 *     }
 * }
 * ~~~
 * Then you can implememt your own `tableName()` for the name of specified table,
 * and `find()` for convenient of IDE.
 * - Instantiation:
 * ~~~php
 * $relation = $user->findOneOrCreate(SingleRelation, ['other_guid' => $other->guid]);
 * ~~~
 * The above is same as the following:
 * ~~~php
 * $relation = SingleRelation::buildRelation($user, $other);
 * ~~~
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
abstract class BaseUserRelationModel extends BaseBlameableModel
{
    use UserRelationTrait;

    /**
     * @var false|string this model will not need to be confirmed any more.
     * If you consider it required, please redeclare it by yourself.
     */
    public $confirmationAttribute = false;

    /**
     * @var false|string this model will not record content any more.
     * If you consider it required, please redeclare it by yourself.
     */
    public $contentAttribute = false;

    /**
     * @var false|string this model will not record updater when being updated.
     * If you consider it required, please redeclare it by yourself.
     */
    public $updatedByAttribute = false;

    /**
     * This method will assign the `$queryClass` property if it is not string.
     */
    public function init()
    {
        if (!is_string($this->queryClass)) {
            $this->queryClass = BaseUserRelationQuery::class;
        }
        if ($this->skipInit) {
            return;
        }
        $this->initUserRelationEvents();
        parent::init();
    }
}
