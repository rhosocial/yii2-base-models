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

namespace rhosocial\base\models\tests\user\relation;

use rhosocial\base\models\tests\data\ar\User;
use rhosocial\base\models\tests\data\ar\relation\UserSingleRelation;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class SingleRelationMultiUserTest extends SingleRelationTestCase
{
    /**
     * @var array
     */
    protected $others = [];
    
    protected function setUp()
    {
        parent::setUp();
        for ($i = 0; $i < 10; $i++) {
            $this->others[] = new User(['password' => \Yii::$app->security->generateRandomString(6)]);
        }
    }
    
    protected function tearDown()
    {
        foreach ($this->others as $key => $other) {
            if ($this->others[$key] instanceof User) {
                try {
                    $this->others[$key]->deregister();
                } catch (\Exception $ex) {

                } finally {
                    $this->others[$key] = null;
                }
            }
        }
        parent::tearDown();
    }
    
    private function registerUsers()
    {
        try {
            $this->assertTrue($this->user->register());
            $this->assertTrue($this->other->register());
            foreach ($this->others as $other) {
                $this->assertTrue($other->register());
            }
        } catch (\Exception $ex) {
            echo $ex->getMessage();
            $this->fail();
        }
    }
    
    private function deregisterUsers()
    {
        try {
            $this->assertTrue($this->user->deregister());
            $this->assertTrue($this->other->deregister());
            foreach ($this->others as $other) {
                $this->assertTrue($other->deregister());
            }
        } catch (\Exception $ex) {
            echo $ex->getMessage();
            $this->fail();
        }
    }
    
    private function generateRelations()
    {
        $userRelations = [];
        foreach ($this->others as $o) {
            $r = $this->prepareSingleRelation($this->user, $o);
            $this->assertTrue($r->save());
            $userRelations[] = $r;
        }
        $otherRelations = [];
        foreach ($this->others as $o) {
            $r = $this->prepareSingleRelationMutually($this->other, $o);
            $this->assertTrue($r[0]->save());
            $this->assertTrue($r[1]->save());
            $otherRelations[] = $r;
        }
        return ['single' => $userRelations, 'mutual' => $otherRelations];
    }
    
    private function destroyRelations($relations = [])
    {
        foreach ($relations as $relation) {
            if ($relation instanceof UserSingleRelation) {
                $this->assertLessThanOrEqual(1, $relation->remove());
            }
            if (is_array($relation)) {
                foreach ($relation as $r) {
                    $this->assertLessThanOrEqual(1, $r->remove());
                }
            }
        }
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-single
     */
    public function testMutual()
    {
        $this->registerUsers();
        $relations = $this->generateRelations();
        foreach ($this->others as $o) {
            $this->assertTrue(UserSingleRelation::isFollowing($this->user, $o));
            $this->assertFalse(UserSingleRelation::isFollowed($this->user, $o));
            
            $this->assertFalse(UserSingleRelation::isFollowing($o, $this->user));
            $this->assertTrue(UserSingleRelation::isFollowed($o, $this->user));
            
            $this->assertTrue(UserSingleRelation::isFollowing($o, $this->other));
            $this->assertTrue(UserSingleRelation::isFollowed($o, $this->other));
        }
        foreach ($this->others as $o) {
            $this->assertTrue(UserSingleRelation::isFriend($this->other, $o));
            $this->assertTrue(UserSingleRelation::isFriend($o, $this->other));
            
            $this->assertFalse(UserSingleRelation::isFriend($this->user, $o));
            $this->assertFalse(UserSingleRelation::isFriend($o, $this->user));
        }
        $this->destroyRelations($relations['single']);
        $this->destroyRelations($relations['mutual']);
        $this->deregisterUsers();
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-single
     */
    public function testQueryInitiatorAndRecipient()
    {
        $this->registerUsers();
        $relations = $this->generateRelations();
        
        $r = UserSingleRelation::find()->
                initiators($this->user)->
                recipients($this->others)->
                initiators()->
                recipients()->
                all();
        $this->assertCount(10, $r);
        foreach ($r as $relation) {
            /* @var $relation UserSingleRelation */
            $this->assertTrue($relation->user->equals($this->user));
        }
        $r = UserSingleRelation::find()->initiators($this->others)->recipients($this->user)->initiators()->recipients()->all();
        $this->assertCount(0, $r);
        
        $r = UserSingleRelation::find()->initiators($this->others)->recipients($this->other)->initiators()->recipients()->all();
        $this->assertCount(10, $r);
        foreach ($r as $relation) {
            /* @var $relation UserSingleRelation */
            $this->assertTrue($relation->recipient->equals($this->other));
        }
        
        $r = UserSingleRelation::find()->initiators($this->other)->recipients($this->others)->initiators()->recipients()->all();
        $this->assertCount(10, $r);
        foreach ($r as $relation) {
            /* @var $relation UserSingleRelation */
            $this->assertTrue($relation->initiator->equals($this->other));
        }
        
        $this->destroyRelations($relations['single']);
        $this->destroyRelations($relations['mutual']);
        $this->deregisterUsers();
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-single
     */
    public function testQueryOpposite()
    {
        $this->registerUsers();
        $relations = $this->generateRelations();
        
        $r = UserSingleRelation::find()->opposites($this->user);
        $this->assertCount(0, $r);
        
        $r = UserSingleRelation::find()->opposites($this->other);
        $this->assertCount(10, $r);
        
        $r = UserSingleRelation::find()->opposites($this->other, $this->others);
        $this->assertCount(10, $r);
        
        $r = UserSingleRelation::find()->opposites($this->other, $this->others[0]);
        $this->assertCount(1, $r);
        
        $this->destroyRelations($relations['single']);
        $this->destroyRelations($relations['mutual']);
        $this->deregisterUsers();
    }
}
