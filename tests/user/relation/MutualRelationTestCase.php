<?php

/**
 *   _   __ __ _____ _____ ___  ____  _____
 *  | | / // // ___//_  _//   ||  __||_   _|
 *  | |/ // /(__  )  / / / /| || |     | |
 *  |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2022 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\tests\user\relation;

use rhosocial\base\models\tests\data\ar\User;
use rhosocial\base\models\tests\data\ar\relation\UserRelation;
use rhosocial\base\models\tests\user\UserTestCase;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class MutualRelationTestCase extends UserTestCase
{
    /**
     * @var User 
     */
    protected $other1 = null;
    
    /**
     * @var User 
     */
    protected $other2 = null;
    
    /**
     * @var UserRelation 
     */
    protected $relationNormal = null;
    
    /**
     * @var UserRelation
     */
    protected $relationSuspend = null;
    
    protected function setUp() : void {
        parent::setUp();
        $this->other1 = new User(['password' => '123456']);
        $this->other2 = new User(['password' => '123456']);
        $this->relationNormal = $this->prepareMutualRelation($this->user, $this->other1);
        $this->relationSuspend = $this->prepareMutualRelationSuspend($this->user, $this->other2);
    }
    
    protected function tearDown() : void {
        if ($this->relationNormal instanceof UserRelation) {
            $this->relationNormal->remove();
        }
        $this->relationNormal = null;
        if ($this->relationSuspend instanceof UserRelation) {
            $this->relationSuspend->remove();
        }
        $this->relationSuspend = null;
        UserRelation::deleteAll();
        if ($this->other1 instanceof User) {
            try {
                $this->other1->deregister();
            } catch (\Exception $ex) {

            } finally {
                $this->other1 = null;
            }
        }
        if ($this->other2 instanceof User) {
            try {
                $this->other2->deregister();
            } catch (\Exception $ex) {

            } finally {
                $this->other2 = null;
            }
        }
        parent::tearDown();
    }
    
    /**
     * Prepare normal mutual relation.
     * @param User $user
     * @param User $other
     * @return UserRelation
     */
    protected function prepareMutualRelation($user, $other)
    {
        return UserRelation::buildNormalRelation($user, $other);
    }
    
    /**
     * Prepare suspend mutual relation
     * @param User $user
     * @param User $other
     * @return UserRelation
     */
    protected function prepareMutualRelationSuspend($user, $other)
    {
        return UserRelation::buildSuspendRelation($user, $other);
    }
}
