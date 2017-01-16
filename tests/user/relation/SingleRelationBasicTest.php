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
use rhosocial\base\models\tests\data\ar\relation\UserSingleRelationSelf;

/**
 * @author vistart <i@vistart.me>
 */
class SingleRelationBasicTest extends SingleRelationTestCase
{
    /**
     * @group user
     * @group relation
     * @group relation-single
     */
    public function testNormal()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertNull(UserSingleRelation::buildNormalRelation($this->user, $this->user));
        $this->assertTrue($this->relation->save());
        
        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
        $this->assertEquals(0, $this->relation->delete());
    }

    /**
     * @group user
     * @group relation
     * @group relation-single
     */
    public function testSuspend()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        
        $this->assertNull(UserSingleRelation::buildSuspendRelation($this->user, $this->other));
        
        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-single
     */
    public function testFindOne()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());
        
        $relation = UserSingleRelation::findOneRelation($this->user, $this->other);
        $this->assertInstanceOf(UserSingleRelation::class, $relation);
        
        $relation = UserSingleRelation::findOneOppositeRelation($this->user, $this->other);
        $this->assertNull($relation);
        
        $relation = UserSingleRelation::findOneRelation($this->other, $this->user);
        $this->assertNull($relation);
        
        $relation = UserSingleRelation::findOneOppositeRelation($this->other, $this->user);
        $this->assertInstanceOf(UserSingleRelation::class, $relation);
        
        $this->assertEquals(1, $this->relation->remove());
        
        $relation = UserSingleRelation::findOneRelation($this->user, $this->other);
        $this->assertNull($relation);
        
        $relation = UserSingleRelation::findOneOppositeRelation($this->user, $this->other);
        $this->assertNull($relation);
        
        $relation = UserSingleRelation::findOneRelation($this->other, $this->user);
        $this->assertNull($relation);
        
        $relation = UserSingleRelation::findOneOppositeRelation($this->other, $this->user);
        $this->assertNull($relation);

        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
        $this->assertEquals(0, $this->relation->delete());
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-single
     */
    public function testGetOpposite()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        
        $this->assertNull($this->relation->getOpposite());        
        $this->assertTrue($this->relation->save());

        $relation = UserSingleRelation::findOneRelation($this->user, $this->other);
        $this->assertInstanceOf(UserSingleRelation::class, $relation);
        $this->assertNull($relation->getOpposite());

        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
        $this->assertEquals(0, $this->relation->delete());
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-single
     */
    public function testFindAll()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());

        $relations = UserSingleRelation::findOnesAllRelations($this->user);
        $this->assertCount(1, $relations);

        $relations = UserSingleRelation::findOnesAllRelations($this->other);
        $this->assertCount(0, $relations);

        $relation = UserSingleRelation::findOneRelation($this->user, $this->other);
        $this->assertInstanceOf(UserSingleRelation::class, $relation);
        $this->assertEquals(1, $relation->remove());

        $relations = UserSingleRelation::findOnesAllRelations($this->user);
        $this->assertCount(0, $relations);

        $relations = UserSingleRelation::findOnesAllRelations($this->other);
        $this->assertCount(0, $relations);

        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
        $this->assertEquals(0, $this->relation->delete());
    }
        
    /**
     * @group user
     * @group relation
     * @group relation-single
     */
    public function testIsFollowed()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());
        
        $this->assertFalse(UserSingleRelation::isFollowed($this->user, $this->other));
        $relation = UserSingleRelation::buildNormalRelation($this->other, $this->user);
        $this->assertTrue($relation->save());
        $this->assertTrue(UserSingleRelation::isFollowed($this->user, $this->other));
        $this->assertEquals(1, $relation->remove());
        $this->assertFalse(UserSingleRelation::isFollowed($this->user, $this->other));

        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
        $this->assertEquals(0, $this->relation->delete());
    }
        
    /**
     * @group user
     * @group relation
     * @group relation-single
     */
    public function testIsFollowing()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());
        
        $this->assertTrue(UserSingleRelation::isFollowing($this->user, $this->other));
        $this->assertFalse(UserSingleRelation::isFollowing($this->other, $this->user));
        $relation = UserSingleRelation::buildNormalRelation($this->other, $this->user);
        $this->assertTrue($relation->save());
        $this->assertTrue(UserSingleRelation::isFollowing($this->user, $this->other));
        $this->assertTrue(UserSingleRelation::isFollowing($this->other, $this->user));
        $this->assertEquals(1, $relation->remove());
        $this->assertTrue(UserSingleRelation::isFollowing($this->user, $this->other));
        $this->assertFalse(UserSingleRelation::isFollowing($this->other, $this->user));

        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
        $this->assertEquals(0, $this->relation->delete());
    }
        
    /**
     * @group user
     * @group relation
     * @group relation-single
     */
    public function testIsMutual()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());
        
        $this->assertFalse(UserSingleRelation::isMutual($this->user, $this->other));
        $this->assertFalse(UserSingleRelation::isMutual($this->other, $this->user));
        $relation = UserSingleRelation::buildNormalRelation($this->other, $this->user);
        $this->assertTrue($relation->save());
        $this->assertTrue(UserSingleRelation::isMutual($this->user, $this->other));
        $this->assertTrue(UserSingleRelation::isMutual($this->other, $this->user));
        $this->assertEquals(1, $relation->remove());
        $this->assertFalse(UserSingleRelation::isMutual($this->user, $this->other));
        $this->assertFalse(UserSingleRelation::isMutual($this->other, $this->user));

        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
        $this->assertEquals(0, $this->relation->delete());
    }

    /**
     * @group user
     * @group relation
     * @group relation-single
     */
    public function testIsFriend()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());
        
        $this->assertFalse(UserSingleRelation::isFriend($this->user, $this->other));
        $this->assertFalse(UserSingleRelation::isFriend($this->other, $this->user));
        $relation = UserSingleRelation::buildNormalRelation($this->other, $this->user);
        $this->assertTrue($relation->save());
        $this->assertTrue(UserSingleRelation::isFriend($this->user, $this->other));
        $this->assertTrue(UserSingleRelation::isFriend($this->other, $this->user));
        $this->assertEquals(1, $relation->remove());
        $this->assertFalse(UserSingleRelation::isFriend($this->user, $this->other));
        $this->assertFalse(UserSingleRelation::isFriend($this->other, $this->user));

        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
        $this->assertEquals(0, $this->relation->delete());
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-single
     */
    public function testRemoveOne()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());

        $relation = UserSingleRelation::findOneRelation($this->user, $this->other);
        $this->assertInstanceOf(UserSingleRelation::class, $relation);
        $this->assertEquals(1, UserSingleRelation::removeOneRelation($this->user, $this->other));
        $relation = UserSingleRelation::findOneRelation($this->user, $this->other);
        $this->assertNull($relation);

        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
        $this->assertEquals(0, $this->relation->delete());
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-single
     */
    public function testRemoveAll()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());
        
        $this->assertEquals(1, UserSingleRelation::removeAllRelations($this->user, $this->other));

        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
        $this->assertEquals(0, $this->relation->delete());
    }

    /**
     * @group user
     * @group relation
     * @group relation-single
     */
    public function testFavorite()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());

        $this->assertFalse($this->relation->getIsFavorite());
        $this->assertEquals(1, $this->relation->setIsFavorite(true));
        $this->assertTrue($this->relation->save());
        $this->assertTrue($this->relation->getIsFavorite());

        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
        $this->assertEquals(0, $this->relation->delete());
    }

    /**
     * @group user
     * @group relation
     * @group relation-single
     */
    public function testRemark()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());

        $this->assertEmpty($this->relation->getRemark());
        $remark = \Yii::$app->security->generateRandomString();
        $this->assertNotEmpty($this->relation->setRemark($remark));
        $this->assertTrue($this->relation->save());
        $this->assertEquals($remark, $this->relation->getRemark());
        
        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
        $this->assertEquals(0, $this->relation->delete());
    }

    /**
     * @group user
     * @group relation
     * @group relation-single
     */
    public function testRelationSelf()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->relation = UserSingleRelationSelf::buildNormalRelation($this->user, $this->user);
        $this->assertTrue($this->relation->save());
        
        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
        $this->assertEquals(0, $this->relation->delete());
    }
}
