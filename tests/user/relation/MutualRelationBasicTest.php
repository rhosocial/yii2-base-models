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

use rhosocial\base\models\tests\data\ar\relation\UserSingleRelation;
use rhosocial\base\models\tests\data\ar\relation\UserRelation;

/**
 * @author vistart <i@vistart.me>
 */
class MutualRelationBasicTest extends MutualRelationTestCase
{
    /**
     * @group user
     * @group relaion
     * @group relation-mutual
     */
    public function testNormal()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other1->register());
        
        $this->assertEquals(UserRelation::$relationMutual, $this->relationNormal->relationType);
        $this->assertCount(0, UserRelation::find()->initiators($this->user)->recipients($this->other1)->all());
        $this->assertTrue($this->relationNormal->save());
        $this->assertCount(1, UserRelation::find()->initiators($this->user)->recipients($this->other1)->all());
        $this->assertCount(1, UserRelation::find()->recipients($this->user)->initiators($this->other1)->all());
        $this->assertTrue($this->user->deregister());
        $this->assertEquals(0, $this->relationNormal->remove());
        $this->assertTrue($this->other1->deregister());
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-mutual
     */
    public function testSuspend()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other2->register());
        $this->assertEquals(UserRelation::$relationMutual, $this->relationSuspend->relationType);
        $this->assertTrue($this->relationSuspend->save());
        $this->assertTrue($this->user->deregister());
        $this->assertEquals(0, $this->relationSuspend->remove());
        $this->assertTrue($this->other2->deregister());
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-mutual
     */
    public function testNormalIsFriend()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other1->register());
        
        $this->assertTrue($this->relationNormal->save());
        $this->assertTrue(UserRelation::isFriend($this->user, $this->other1));
        $this->assertTrue(UserRelation::isFriend($this->other1, $this->user));
        
        $this->assertTrue($this->user->deregister());
        $this->assertEquals(0, $this->relationNormal->remove());
        $this->assertTrue($this->other1->deregister());
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-mutual
     */
    public function testSuspendIsFriend()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other2->register());
        
        $this->assertTrue($this->relationSuspend->save());
        $this->assertFalse(UserRelation::isFriend($this->user, $this->other2));
        $this->assertFalse(UserRelation::isFriend($this->other2, $this->user));
        
        $this->assertTrue($this->user->deregister());
        $this->assertEquals(0, $this->relationSuspend->remove());
        $this->assertTrue($this->other2->deregister());
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-mutual
     * @depends testSuspendIsFriend
     */
    public function testSuspendToNormal()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other2->register());
        
        $this->assertTrue($this->relationSuspend->save());
        $this->assertTrue(UserRelation::transformSuspendToNormal($this->relationSuspend));
        $this->assertTrue(UserRelation::isFriend($this->user, $this->other2));
        $this->assertTrue(UserRelation::isFriend($this->other2, $this->user));
        $this->assertEquals(UserRelation::$mutualTypeNormal, $this->relationSuspend->getMutualType());
        
        $this->assertTrue($this->user->deregister());
        $this->assertEquals(0, $this->relationSuspend->remove());
        $this->assertTrue($this->other2->deregister());
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-mutual
     * @depends testNormalIsFriend
     */
    public function testNormalToSuspend()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other1->register());
        
        $this->assertTrue($this->relationNormal->save());
        $this->assertTrue(UserRelation::revertNormalToSuspend($this->relationNormal));
        $this->assertFalse(UserRelation::isFriend($this->user, $this->other1));
        $this->assertFalse(UserRelation::isFriend($this->other1, $this->user));
        $this->assertEquals(UserRelation::$mutualTypeSuspend, $this->relationNormal->getMutualType());
        
        $this->assertTrue($this->user->deregister());
        $this->assertEquals(0, $this->relationNormal->remove());
        $this->assertTrue($this->other1->deregister());
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-mutual
     */
    public function testNormalInvalid()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other1->register());
        
        $this->assertFalse(UserRelation::revertNormalToSuspend(null));
        $this->assertFalse(UserRelation::revertNormalToSuspend($this->user));
        $this->assertFalse(UserRelation::revertNormalToSuspend($this->relationNormal));
        $this->assertFalse(UserRelation::revertNormalToSuspend(UserSingleRelation::buildNormalRelation($this->user, $this->other1)));
        
        $this->assertTrue($this->user->deregister());
        $this->assertEquals(0, $this->relationNormal->remove());
        $this->assertTrue($this->other1->deregister());
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-mutual
     */
    public function testSuspendInvalid()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other2->register());
        
        $this->assertFalse(UserRelation::transformSuspendToNormal(null));
        $this->assertFalse(UserRelation::transformSuspendToNormal($this->user));
        $this->assertFalse(UserRelation::transformSuspendToNormal($this->relationSuspend));
        $this->assertFalse(UserRelation::transformSuspendToNormal(UserSingleRelation::buildNormalRelation($this->user, $this->other2)));
        
        $this->assertTrue($this->user->deregister());
        $this->assertEquals(0, $this->relationSuspend->remove());
        $this->assertTrue($this->other2->deregister());
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-mutual
     */
    public function testInsertRelation()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other1->register());
        
        $this->assertFalse(UserRelation::insertRelation(null));
        $this->assertTrue(UserRelation::insertRelation($this->relationNormal));
        try {
            UserRelation::insertRelation($this->relationNormal);
            $this->fail();
        } catch (\Exception $ex) {
            $this->assertTrue(true);
        }
        
        $this->assertTrue($this->user->deregister());
        $this->assertEquals(0, $this->relationNormal->remove());
        $this->assertTrue($this->other1->deregister());
    }
}