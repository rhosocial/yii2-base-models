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

namespace rhosocial\base\models\tests\user\relation;

use rhosocial\base\models\tests\data\ar\User;
use rhosocial\base\models\tests\data\ar\relation\UserSingleRelation;
use rhosocial\base\models\tests\user\UserTestCase;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class SingleRelationTestCase extends UserTestCase
{
    /**
     * @var User 
     */
    protected $other = null;
    
    /**
     * @var UserSingleRelation 
     */
    protected $relation = null;
    
    protected function setUp() : void {
        parent::setUp();
        $this->other = new User(['password' => '123456']);
        $this->relation = $this->prepareSingleRelation($this->user, $this->other);
    }
    
    /**
     * Prepare single relation.
     * @param User $user
     * @param User $other
     * @return UserSingleRelation
     */
    protected function prepareSingleRelation($user, $other)
    {
        return UserSingleRelation::buildNormalRelation($user, $other);
    }

    /**
     * Prepare single relation mutually.
     * @param User $user
     * @param User $other
     * @return UserSingleRelation
     */
    protected function prepareSingleRelationMutually($user, $other)
    {
        return [$this->prepareSingleRelation($user, $other), $this->prepareSingleRelation($other, $user)];
    }
    
    protected function tearDown() : void {
        if ($this->relation instanceof UserSingleRelation) {
            $this->relation->remove();
        }
        $this->relation = null;
        UserSingleRelation::deleteAll();
        if ($this->other instanceof User) {
            try {
                $this->other->deregister();
            } catch (\Exception $ex) {

            } finally {
                $this->other = null;
            }
        }
        parent::tearDown();
    }
}
