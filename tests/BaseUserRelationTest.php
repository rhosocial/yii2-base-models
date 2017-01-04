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

namespace rhosocial\base\models\tests;

use rhosocial\base\models\tests\data\ar\User;
use rhosocial\base\models\tests\data\ar\UserRelation;
use rhosocial\base\models\tests\data\ar\UserSingleRelation;
use rhosocial\base\models\tests\data\ar\UserRelationGroup;

/**
 * @author vistart <i@vistart.me>
 */
class BaseUserRelationTest extends TestCase
{
    private function prepareUsers()
    {
        $user = new User(['password' => '123456']);
        $other_user = new User(['password' => '123456']);
        $this->assertTrue($user->register());
        $this->assertTrue($other_user->register());
        return [$user, $other_user];
    }
    
    private function destroyUsers($users = [])
    {
        foreach ($users as $user)
        {
            $this->assertTrue($user->deregister());
        }
    }
    
    private function prepareSingleRelationModels($user, $other)
    {
        $relation = UserSingleRelation::buildNormalRelation($user, $other);
        if ($relation->save()) {
            $this->assertTrue(true);
        } else {
            var_dump($relation->rules());
            var_dump($relation->errors);
            $this->fail('Single Relation Save Failed.');
        }
        return $relation;
    }
    
    private function prepareMutualRelationModels($user, $other, $bi_type = null)
    {
        if (!$bi_type) {
            $bi_type = UserRelation::$mutualTypeNormal;
        }
        switch ($bi_type) {
            case UserRelation::$mutualTypeNormal:
                $relation = UserRelation::buildNormalRelation($user, $other);
                break;
            case UserRelation::$mutualTypeSuspend:
                $relation = UserRelation::buildSuspendRelation($user, $other);
                break;
        }
        if ($relation->save()) {
            $this->assertTrue(true);
            $opposite = UserRelation::findOneOppositeRelation($user, $other);
            $this->assertInstanceOf(UserRelation::class, $opposite);
            $opposite = $relation->opposite;
            $this->assertInstanceOf(UserRelation::class, $opposite);
            $opposite = UserRelation::find()->opposite($user, $other);
            $this->assertInstanceOf(UserRelation::class, $opposite);
            $opposites = UserRelation::find()->opposites($user);
            $this->assertEquals(1, count($opposites));
        } else {
            var_dump($relation->rules());
            var_dump($relation->errors);
            $this->fail('Mutual Relation Save Failed.');
        }
        return [$relation, $opposite];
    }
    
    /**
     * @group user
     * @group relation
     */
    public function testNew()
    {
        $users = $this->prepareUsers();
        $user = $users[0];
        $relation = $user->create(UserRelation::class);
        $opposite = $relation->opposite;
        $this->assertNull($opposite);
        $other = $users[1];
        $relations = $this->prepareMutualRelationModels($user, $other);
        $this->destroyUsers($users);
    }
    
    /**
     * 测试删除其中一个关系。
     * 正常情况下，删除一个关系，则相反关系也应一并删除。
     * @depends testNew
     * @group relation
     */
    public function testRemoveOne()
    {
        $users = $this->prepareUsers();
        $user = $users[0];
        $other = $users[1];
        $this->prepareMutualRelationModels($user, $other);
        $this->assertTrue(UserRelation::isMutual($user, $other));
        $this->assertEquals(1, UserRelation::removeOneRelation($user, $other));
        $this->assertFalse(UserRelation::isMutual($user, $other));
        $this->assertNull(UserRelation::findOneRelation($user, $other));
        $this->assertNull(UserRelation::findOneOppositeRelation($user, $other));
        $relations = $this->prepareMutualRelationModels($user, $other);
        $this->assertEquals(1, $relations[0]->remove());
        $this->destroyUsers($users);
    }
    
    /**
     * @depends testRemoveOne
     * @group relation
     */
    public function testDeregisterOne()
    {
        $users = $this->prepareUsers();
        $user = $users[0];
        $other = $users[1];
        $this->prepareMutualRelationModels($user, $other);
        if ($user->deregister()) {
            $this->assertTrue(true);
            $this->assertTrue($other->deregister());
        } else {
            $this->assertTrue(false);
            var_dump($user->errors);
        }
    }
    
    /**
     * @depends testDeregisterOne
     * @group relation
     */
    public function testFavoriteAndRemark()
    {
        $users = $this->prepareUsers();
        $relations = $this->prepareMutualRelationModels($users[0], $users[1]);
        $favoriteAttribute = $relations[0]->favoriteAttribute;
        $this->assertEquals(0, $relations[0]->$favoriteAttribute);
        $this->assertFalse($relations[0]->isFavorite);
        $relations[0]->isFavorite = true;
        $this->assertTrue($relations[0]->save());
        $this->assertEquals(1, $relations[0]->$favoriteAttribute);
        $this->assertTrue($relations[0]->isFavorite);
        $this->assertEmpty($relations[0]->remark);
        $relations[0]->remark = 'favorite';
        $this->assertTrue($relations[0]->save());
        $this->assertEquals('favorite', $relations[0]->remark);
        $this->destroyUsers($users);
    }
    
    /**
     * @depends testFavoriteAndRemark
     * @group relation
     * @group relation-single
     */
    public function testSingleRelation()
    {
        $users = $this->prepareUsers();
        $user = $users[0];
        $other = $users[1];
        $relation = $this->prepareSingleRelationModels($user, $other);
        $this->assertFalse($relation->isNewRecord);
        $r = UserSingleRelation::findOneRelation($user, $other);
        $this->assertEquals($relation->guid, $r->guid);
        $createdAtAttribute = $relation->createdAtAttribute;
        //$updatedAtAttribute = $relation->updatedAtAttribute;
        $previous = date("Y-m-d H:i:s", strtotime($relation->$createdAtAttribute . '-1 second'));
        $next = date("Y-m-d H:i:s", strtotime($relation->$createdAtAttribute . '+1 second'));
        $query = UserSingleRelation::find()->createdAt($previous, $next)->initiators($user)->recipients($other);
        //echo $query->createCommand()->getRawSql();
        $r = UserSingleRelation::find()->createdAt($previous, $next)->initiators($user)->recipients($other)->one();
        $this->assertInstanceOf(UserSingleRelation::className(), $r);
        $this->assertEquals($relation->guid, $r->guid);
        $r = UserSingleRelation::find()->updatedAt($previous, $next)->initiators($user)->recipients($other)->one();
        $this->assertInstanceOf(UserSingleRelation::className(), $r);
        $this->assertEquals($relation->guid, $r->guid);
        $r = UserSingleRelation::find()->createdAt($next)->initiators($user)->recipients($other)->one();
        $this->assertNull($r);
        $r = UserSingleRelation::find()->createdAt(null, $previous)->initiators($user)->recipients($other)->one();
        $this->assertNull($r);
        $r = UserSingleRelation::find()->createdAt($next, $previous)->initiators($user)->recipients($other)->one();
        $this->assertNull($r);
        $r = UserSingleRelation::find()->updatedAt($next)->initiators($user)->recipients($other)->one();
        $this->assertNull($r);
        $r = UserSingleRelation::find()->updatedAt(null, $previous)->initiators($user)->recipients($other)->one();
        $this->assertNull($r);
        $r = UserSingleRelation::find()->updatedAt($next, $previous)->initiators($user)->recipients($other)->one();
        $this->assertNull($r);
        $this->destroyUsers($users);
    }
    
    /**
     * @depends testSingleRelation
     * @group relation
     * @group relation-single
     */
    public function testSingleRelationStatic()
    {
        $users = $this->prepareUsers();
        $initiator = $users[0];
        $recipient = $users[1];
        $relation = $this->prepareSingleRelationModels($initiator, $recipient);
        $this->assertTrue(UserSingleRelation::isFollowing($initiator, $recipient));
        $this->assertFalse(UserSingleRelation::isFollowed($initiator, $recipient));
        $this->assertFalse(UserSingleRelation::isMutual($initiator, $recipient));
        $this->assertFalse(UserSingleRelation::isFriend($initiator, $recipient));
        $this->assertEquals(1, $relation->delete());
        $inverse = $this->prepareSingleRelationModels($recipient, $initiator);
        $this->assertFalse(UserSingleRelation::isFollowing($initiator, $recipient));
        $this->assertTrue(UserSingleRelation::isFollowed($initiator, $recipient));
        $this->assertFalse(UserSingleRelation::isMutual($initiator, $recipient));
        $this->assertFalse(UserSingleRelation::isFriend($initiator, $recipient));
        $relation = $this->prepareSingleRelationModels($initiator, $recipient);
        $this->assertTrue(UserSingleRelation::isFollowing($initiator, $recipient));
        $this->assertTrue(UserSingleRelation::isFollowed($initiator, $recipient));
        $this->assertTrue(UserSingleRelation::isMutual($initiator, $recipient));
        $this->assertTrue(UserSingleRelation::isFriend($initiator, $recipient));
        
        $this->assertNull(UserSingleRelation::buildSuspendRelation($initiator, $recipient));
        $this->destroyUsers($users);
    }

    /**
     * @depends testSingleRelationStatic
     * @group relation
     * @group relation-mutual
     */
    public function testMutualRelation()
    {
        $users = $this->prepareUsers();
        $user = $users[0];
        $other = $users[1];
        // 测试双向关系类型和重建。
        $relations = $this->prepareMutualRelationModels($user, $other, UserRelation::$mutualTypeNormal);
        $mutualTypeAttribute = $relations[0]->mutualTypeAttribute;
        $this->assertEquals(UserRelation::$mutualTypeNormal, $relations[0]->$mutualTypeAttribute);
        $rguid = $relations[0]->guid;
        $oguid = $relations[1]->guid;
        $rcreatedAt = $relations[0]->createdAtAttribute;
        $rcreatetime = $relations[0]->$rcreatedAt;
        $rupdatedAt = $relations[0]->updatedAtAttribute;
        $rupdatetime = $relations[0]->$rupdatedAt;
        //$this->assertGreaterThanOrEqual(1, $relations[0]->remove());
        sleep(1); //延时一秒，测试修改。
        $relations = $this->prepareMutualRelationModels($user, $other, UserRelation::$mutualTypeSuspend);
        $this->assertEquals($rguid, $relations[0]->guid);
        $this->assertEquals($oguid, $relations[1]->guid);
        $this->assertEquals($rcreatetime, $relations[0]->$rcreatedAt);
        $this->assertNotEquals($rupdatetime, $relations[0]->$rupdatedAt);
        $this->assertEquals(UserRelation::$mutualTypeSuspend, $relations[0]->$mutualTypeAttribute);
        $this->assertGreaterThanOrEqual(1, $relations[0]->remove());
        // 测试上限。
        $this->destroyUsers($users);
    }
    
    /**
     * @depends testMutualRelation
     * @group relation
     * @group relation-mutual
     */
    public function testMutualRelationStatic()
    {
        $users = $this->prepareUsers();
        $initiator = $users[0];
        $recipient = $users[1];
        
        $relations = $this->prepareMutualRelationModels($initiator, $recipient, UserRelation::$mutualTypeNormal);
        $this->assertTrue(UserRelation::isFollowing($initiator, $recipient));
        $this->assertTrue(UserRelation::isFollowed($initiator, $recipient));
        $this->assertTrue(UserRelation::isFollowing($recipient, $initiator));
        $this->assertTrue(UserRelation::isFollowed($recipient, $initiator));
        
        $this->assertTrue(UserRelation::isMutual($initiator, $recipient));
        $this->assertTrue(UserRelation::isMutual($recipient, $initiator));
        $this->assertTrue(UserRelation::isFriend($initiator, $recipient));
        $this->assertTrue(UserRelation::isFriend($recipient, $initiator));
        
        $relations = $this->prepareMutualRelationModels($initiator, $recipient, UserRelation::$mutualTypeSuspend);
        $this->assertTrue(UserRelation::isFollowing($initiator, $recipient));
        $this->assertTrue(UserRelation::isFollowed($initiator, $recipient));
        $this->assertTrue(UserRelation::isFollowing($recipient, $initiator));
        $this->assertTrue(UserRelation::isFollowed($recipient, $initiator));
        
        $this->assertTrue(UserRelation::isMutual($initiator, $recipient));
        $this->assertTrue(UserRelation::isMutual($recipient, $initiator));
        $this->assertFalse(UserRelation::isFriend($initiator, $recipient));
        $this->assertFalse(UserRelation::isFriend($recipient, $initiator));
        
        $this->destroyUsers($users);
    }

    /**
     * @depends testMutualRelationStatic
     * @group relation
     */
    public function testRelationGroup()
    {
        // 准备两个用户
        $users = $this->prepareUsers();
        // 准备两个用户之间的双向关系
        $relations = $this->prepareMutualRelationModels($users[0], $users[1]);
        // 第一个用户的主动关系
        $relation = $relations[0];
        // 当前关系组应为空数组
        $groupsAttribute = $relation->multiBlamesAttribute;
        $this->assertEquals('', $relation->$groupsAttribute);
        // 当前未分组用户应为 1，即对方
        $members = $relation->getNonGroupMembers();
        $this->assertEquals(1, count($members));
        // 新建一个组，在保存前，当前关系找不到该组。
        $group = $users[0]->create(UserRelationGroup::className(), ['content' => 'classmate']);
        $group1 = $users[0]->create(UserRelationGroup::className(), ['content' => 'relative']);
        $this->assertEmpty($relation->getGroupMembers($group));
        $this->assertEmpty(UserRelation::getGroup($group->guid));
        $this->assertEmpty($relation->getGroupMembers($group1));
        $this->assertEmpty(UserRelation::getGroup($group1->guid));
        if ($group->save()) {
            $this->assertTrue(true);
        } else {
            var_dump($group->errors);
            $this->assertFalse(true);
        }
        if ($group1->save()) {
            $this->assertTrue(true);
        } else {
            var_dump($group1->errors);
            $this->assertFalse(true);
        }
        // 保存后也应当找不到，因为当前关系没有添加任何组。
        $this->assertEmpty($relation->getGroupMembers($group));
        // 不过
        $this->assertNotEmpty(UserRelation::getGroup($group->guid));
        // 添加一个关系组，并获得添加后的关系组数组。
        $relationGroups = $relation->addGroup($group);
        // 测试长度。
        $mbAttribute = $relations[0]->multiBlamesAttribute;
        $this->assertEquals($relations[0]->getGroupsCount() * 16, strlen($relations[0]->$mbAttribute));
        // 此时应该有 1 个元素，即 1 个组。
        $this->assertEquals(1, count($relationGroups));
        $this->assertEquals($group->guid, $relationGroups[0]);
        $this->assertEquals($group->guid, $relation->groupGuids[0]);
        // 再添加一个组，并获得添加后的关系组数组。
        $relationGroups = $relation->addGroup($group1);
        $this->assertEquals($relations[0]->getGroupsCount() * 16, strlen($relations[0]->$mbAttribute));
        // 此时应该有 2 个元素，即 2 个组。
        $this->assertEquals(2, count($relationGroups));
        $this->assertEquals($group1->guid, $relationGroups[1]);
        $this->assertEquals($group1->guid, $relation->groupGuids[1]);
        if ($relation->save()) {
            $this->assertTrue(true);
        } else {
            var_dump($relation->errors);
            $this->assertFalse(true);
        }
        $baseQuery = UserRelation::find()->initiators($users[0]->guid)->recipients($users[1]->guid);
        $query = $baseQuery->groups($relation->groupGuids[0]);
        $commandQuery = clone $query;
        //echo $commandQuery->createCommand()->getRawSql() . "\n";
        $this->assertEquals(1, count($query->all()));
        $baseQuery = UserRelation::find()->initiators($users[0]->guid)->recipients($users[1]->guid);
        $query = $baseQuery->groups($relation->groupGuids[1]);
        $commandQuery = clone $query;
        //echo $commandQuery->createCommand()->getRawSql() . "\n";
        $this->assertEquals(1, count($query->all()));
        $baseQuery = UserRelation::find()->initiators($users[0]->guid)->recipients($users[1]->guid);
        $query = $baseQuery->groups($relation->groupGuids);
        $commandQuery = clone $query;
        //echo $commandQuery->createCommand()->getRawSql() . "\n";
        $this->assertEquals(1, count($query->all()));
        $baseQuery = UserRelation::find()->initiators($users[0]->guid)->recipients($users[1]->guid);
        $query = $baseQuery->groups("g");
        $this->assertEquals(0, count($query->all()));
        $baseQuery = UserRelation::find()->initiators($users[0]->guid)->recipients($users[1]->guid);
        $query = $baseQuery->groups();
        $this->assertEquals(0, count($query->all()));
        // 删除成功
        $this->assertGreaterThanOrEqual(1, $group->delete());
        $this->assertGreaterThanOrEqual(1, $group1->delete());
        // 虽然关系组删除了，但不会影响涉及到的关系，所以包含了被删除的关系组的关系，其关系组列表依然包含该关系组。
        // 因此，此时直接获取关系组列表，被删除的组GUID依然在列表中。
        $this->assertNotEmpty($relation->groupGuids);
        // 如果要主动将失效的关系组剔除出关系组列表，可以在获取关系组列表时，强制检查有效性：
        $groups = $relation->getGroupGuids(true);
        $this->assertEmpty($groups); // 此时应该为空。
        $query = UserRelation::find()->groups($groups)->all();
        // 此时未分组关系应该有一个。
        $this->assertEquals(1, count($query));
        // 而且标明列表已经改变了。
        $this->assertTrue($relation->blamesChanged);
        $this->destroyUsers($users);
    }

    /**
     * @depends testRelationGroup
     * @group relation
     */
    public function testMultiRelationGroups()
    {
        $users = $this->prepareUsers();
        $user = $users[0];
        $other = $users[1];
        $relations = $this->prepareMutualRelationModels($user, $other);
        $relation = $relations[0];
        $group = ['content' => 'new group'];
        $groups = $relation->addOrCreateGroup($group);
        $this->assertTrue(is_array($groups));
        $this->assertTrue(in_array($group->guid, $groups));
        $g = UserRelation::getGroup($group->guid);
        $createdByAttribute = $g->createdByAttribute;
        $this->assertEquals($user->guid, $g->$createdByAttribute);
        $groups = $relation->removeGroup($group);
        $this->assertFalse(in_array($group->guid, $groups));
        $groups = $relation->removeGroup($group->guid);
        $this->assertFalse(in_array($group->guid, $groups));
        $groups = $relation->getAllGroups();
        $this->assertNotEmpty($groups);
        $this->assertEquals($group->guid, $groups[0]->guid);
        $this->destroyUsers($users);
    }
}