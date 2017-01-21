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
use rhosocial\base\models\tests\data\ar\relation\UserRelationGroup;

/**
 * @author vistart <i@vistart.me>
 */
class SingleRelationGroupTest extends SingleRelationTestCase
{
    protected function tearDown()
    {
        UserRelationGroup::deleteAll();
        parent::tearDown();
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-single
     * @group relation-group
     */
    public function testNew()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());
        $group = $this->user->create(UserRelationGroup::class, ['content' => \Yii::$app->security->generateRandomString()]);
        $this->assertTrue($group->save());
        $this->assertEquals(1, $group->delete());
        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-single
     * @group relation-group
     */
    public function testCreateInvalidGroup()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());
        
        try {
            $group = '';
            UserSingleRelation::createGroup($group);
            $this->fail();
        } catch (\Exception $ex) {
            $this->assertEquals('the type of user instance must be the extended class of BaseUserModel.', $ex->getMessage());
        }
        
        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-single
     * @group relation-group
     */
    public function testCreateGroupAndAdd()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());
        
        // Create a group
        $content = \Yii::$app->security->generateRandomString();
        $group = UserSingleRelation::createGroup($this->user, ['content' => $content]);
        $this->assertInstanceOf(UserRelationGroup::class, $group);
        $this->assertEquals($content, $group->content);
        $this->assertTrue($group->save());
        
        // Create a group by another user
        $anotherContent = \Yii::$app->security->generateRandomString();
        $anotherGroup = UserSingleRelation::createGroup($this->other, ['content' => $anotherContent]);
        $this->assertInstanceOf(UserRelationGroup::class, $anotherGroup);
        $this->assertEquals($anotherContent, $anotherGroup->content);
        $this->assertTrue($anotherGroup->save());
        
        // Add a group
        $groupGuids = $this->relation->addGroup($group);
        $this->assertEquals($group->getGUID(), $groupGuids[0]);
        $this->assertTrue($this->relation->save());
        
        // Check whether the group exists.
        $groups = UserSingleRelation::getAllGroups($this->user);
        $this->assertCount(1, $groups);
        $this->assertEquals($group->getGUID(), $groups[0]->getGUID());
        
        // This relation doesn't belong to another group
        $this->assertNotEquals($anotherGroup->getGUID(), $groups[0]->getGUID());
        
        // Check exists in another way.
        $groups = $this->relation->getOwnGroups();
        $this->assertCount(1, $groups);
        $this->assertEquals($group->getGUID(), $groups[0]->getGUID());
        
        // This relation doesn't belong to another group
        $this->assertNotEquals($anotherGroup->getGUID(), $groups[0]->getGUID());
        
        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-single
     * @group relation-group
     */
    public function testGetOrCreateGroup()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());
        
        // This process will throw an exception: User Not Specified.
        try {
            $group = UserSingleRelation::getOrCreateGroup(['content' => \Yii::$app->security->generateRandomString()], null);
            $this->fail();
        } catch (\Exception $ex) {
            $this->assertEquals('the type of user instance must be the extended class of BaseUserModel.', $ex->getMessage());
        }
        
        $group = UserSingleRelation::getOrCreateGroup(null, $this->user);
        $this->assertNull($group);
        
        // Create a group and get it.
        $group = UserSingleRelation::createGroup($this->user, ['content' => \Yii::$app->security->generateRandomString()]);
        $this->assertTrue($group->save());
        $this->assertInstanceOf(UserRelationGroup::class, UserSingleRelation::getOrCreateGroup($group->getGUID(), $this->user));
        $this->assertEquals($group->getGUID(), (UserSingleRelation::getOrCreateGroup($group->getGUID(), $this->user)->getGUID()));
        $this->assertEquals(1, $group->delete());
        
        // Create a group with a configuration array
        $group = UserSingleRelation::getOrCreateGroup(['content' => \Yii::$app->security->generateRandomString()], $this->user);
        $this->assertInstanceOf(UserRelationGroup::class, $group);
        $this->assertTrue($group->save());
        $this->assertEquals(1, $group->delete());
        
        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-single
     * @group relation-group
     */
    public function testAddOrCreateGroup()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());
        $this->assertTrue($this->user->equals($this->relation->host));
        
        $content = \Yii::$app->security->generateRandomString();
        $group = ['content' => $content];
        $groupGuids = $this->relation->addOrCreateGroup($group);
        $this->assertCount(1, $groupGuids);
        $groups = $this->relation->getOwnGroups();
        $this->assertCount(1, $groups);
        $this->assertEquals($groupGuids[0], $groups[0]->getGUID());
        
        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-single
     * @group relation-group
     */
    public function testGetNonGroups()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());
        
        $this->assertCount(1, UserSingleRelation::getNonGroupMembers($this->user));
        $this->assertCount(0, UserSingleRelation::getNonGroupMembers($this->other));
        
        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
    }
}