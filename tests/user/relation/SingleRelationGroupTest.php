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

use rhosocial\base\helpers\Number;
use rhosocial\base\models\tests\data\ar\relation\UserSingleRelation;
use rhosocial\base\models\tests\data\ar\relation\UserRelationGroup;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class SingleRelationGroupTest extends SingleRelationTestCase
{
    protected function tearDown() : void {
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
    public function testGetInvalidGroup()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());
        
        $this->assertNull(UserSingleRelation::getBlame(null));
        
        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-single
     * @group relation-group
     */
    public function testIsInvalidGroupContained()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());
        
        $this->assertFalse($this->relation->isGroupContained(null));
        
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
        $this->assertInstanceOf(UserRelationGroup::class, $group);
        
        $this->assertNull(UserSingleRelation::getOrCreateGroup(false, $this->user));
        
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
    public function testAddOrCreateGroupAddExist()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());
        
        $group = UserSingleRelation::createGroup($this->user, ['content' => \Yii::$app->security->generateRandomString()]);
        $this->assertTrue($group->save());
        $guids = $this->relation->addOrCreateGroup($group);
        $this->assertCount(1, $guids);
        $this->assertTrue($this->relation->save());
        $this->assertTrue(in_array($group->getGUID(), $guids));
        
        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-single
     * @group relation-group
     */
    public function testGetGroupMembers()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());
        
        $group = ['content' => \Yii::$app->security->generateRandomString()];
        $group = UserSingleRelation::createGroup($this->user, $group);
        $this->assertInstanceOf(UserRelationGroup::class, $group);
        $this->assertTrue($group->save());
        $this->relation->addGroup($group);
        
        $members = UserSingleRelation::getGroupMembers($group);
        $this->assertCount(0, $members);
        
        $this->assertTrue($this->relation->save());
        $members = UserSingleRelation::getGroupMembers($group);
        $this->assertCount(1, $members);
        $this->assertTrue($this->relation->equals($members[0]));
        
        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-single
     * @group relation-group
     */
    public function testGetEmptyGroupMembers()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());
        
        $members = UserSingleRelation::getGroupMembers(Number::guid());
        $this->assertEquals([], $members);
        
        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-single
     * @group relation-group
     */
    public function testGetNonGroupMembers()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());
        
        $this->assertCount(1, UserSingleRelation::getNonGroupMembers($this->user));
        $this->assertCount(0, UserSingleRelation::getNonGroupMembers($this->other));
        
        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-single
     * @group relation-group
     */
    public function testIsGroupContained()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());
        
        $group = ['content' => \Yii::$app->security->generateRandomString()];
        $group = UserSingleRelation::createGroup($this->user, $group);
        $this->assertInstanceOf(UserRelationGroup::class, $group);
        $this->assertTrue($group->save());
        $this->assertFalse($this->relation->isGroupContained($group));
        $this->relation->addGroup($group);
        $this->assertTrue($this->relation->save());
        $this->assertEquals(0, $this->relation->isGroupContained($group));
        
        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-single
     * @group relation-group
     */
    public function testRemoveGroup()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());
        
        $groups = [];
        $groupGUIDs = [];
        for ($i = 0; $i < 3; $i++) {
            $groups[$i] = ['content' => \Yii::$app->security->generateRandomString()];
            $groupGUIDs[$i] = $this->relation->addOrCreateGroup($groups[$i])[$i];
        }
        $this->assertTrue($this->relation->save());
        
        foreach ($groupGUIDs as $guid) {
            $this->assertInstanceOf(UserRelationGroup::class, $this->relation->getGroup($guid));
        }
        
        $this->assertCount(3, $this->relation->getGroupGuids());
        $guids = $this->relation->getGroupGuids(true);
        $this->assertCount(3, $guids);
        $this->assertEquals($groupGUIDs, $guids);
        
        // Remove 2nd group
        $guid = $guids[1];
        // Remove group instance.
        $this->relation->removeGroup($this->relation->getGroup($guid));
        $this->assertTrue($this->relation->save());
        
        // There are two groups left.
        $guids = $this->relation->getGroupGuids();
        $this->assertCount(2, $guids);
        $this->assertFalse(in_array($guid, $guids));
        
        // This group exists after removing it from relation group list.
        $this->assertTrue(UserRelationGroup::find()->guid($guid)->exists());
        
        // Remove 3rd group
        $guid = $guids[1];
        // Remove group guid
        $this->relation->removeGroup($guid);
        $this->assertTrue($this->relation->save());
        
        // There is only one group left.
        $guids = $this->relation->getGroupGuids();
        $this->assertCount(1, $guids);
        $this->assertFalse(in_array($guid, $guids));
        
        // This group exists after removing it from relation group list.
        $this->assertTrue(UserRelationGroup::find()->guid($guid)->exists());
        
        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-single
     * @group relation-group
     */
    public function testEmptyGroups()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());
        
        $groups = [];
        for ($i = 0; $i < 5; $i++)
        {
            $groups[$i] = UserSingleRelation::createGroup($this->user, ['content' => \Yii::$app->security->generateRandomString()]);
            $this->assertTrue($groups[$i]->save());
        }
        $emptyGroups = UserSingleRelation::getEmptyGroups($this->user);
        $this->assertCount(5, $emptyGroups, 'There should be 5 groups.');
        $this->assertCount(0, UserSingleRelation::getEmptyGroups($this->other), "There isn't any groups.");
        
        for ($i = 0; $i < 5; $i++)
        {
            $guids = $this->relation->addGroup($emptyGroups[0]);
            $this->assertCount($i + 1, $guids);
            $this->assertEquals($guids[$i], $emptyGroups[0]->getGUID());
            $this->assertTrue($this->relation->save());
            
            $guids = $this->relation->getGroupGuids();
            $this->assertEquals($guids, $this->relation->getGroupGuids(true));
            $emptyGroups = UserSingleRelation::getEmptyGroups($this->user);
            $this->assertCount(4 - $i, $emptyGroups, "There shoud be " . (4 - $i) . " groups.");
            $this->assertCount(0, UserSingleRelation::getEmptyGroups($this->other), "There isn't any groups.");
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
    public function testFindByGroup()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());
        
        $group = ['content' => \Yii::$app->security->generateRandomString()];
        $guids = $this->relation->addOrCreateGroup($group);
        $this->assertCount(1, $guids);
        $this->assertTrue(in_array($group->getGUID(), $guids));
        
        $relation = UserSingleRelation::find()->groups()->one();
        $this->assertInstanceOf(UserSingleRelation::class, $relation);
        $relation = UserSingleRelation::find()->groups([$group->getGUID()])->one();
        $this->assertNull($relation);
        
        $this->assertTrue($this->relation->save());
        
        $relation = UserSingleRelation::find()->groups()->one();
        $this->assertNull($relation);
        $relation = UserSingleRelation::find()->groups([$group->getGUID()])->one();
        $this->assertInstanceOf(UserSingleRelation::class, $relation);
        
        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
    }
}
