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
use rhosocial\base\models\tests\data\ar\UserComment;

/**
 * @author vistart <i@vistart.me>
 */
class BaseUserCommentTest extends TestCase
{
    private function prepareUser()
    {
        $user = new User(['password' => '123456']);
        $this->assertTrue($user->register());
        return $user;
    }
    
    private function prepareComment($user)
    {
        $comment = $user->create(UserComment::class, ['content' => 'comment']);
        return $comment;
    }
    
    private function prepareSubComment($comment)
    {
        $sub = $comment->bear(['class' => 1, 'content' => 'sub', $comment->createdByAttribute => $comment->{$comment->createdByAttribute}]);
        $sub->content = 'sub';
        return $sub;
    }
    
    /**
     * @group user
     * @group comment
     */
    public function testNew()
    {
        $user = $this->prepareUser();
        $comment = $this->prepareComment($user);
        $subComment = $this->prepareSubComment($comment);
        if ($result = $comment->save()) {
            $this->assertTrue($result);
        } else {
            var_dump($comment->errors);
            $this->fail();
        }
        if ($result = $subComment->save()) {
            $this->assertTrue($result);
        } else {
            var_dump($subComment->errors);
            $this->fail();
        }
        $rules = [
            [[$subComment->parentAttribute], 'string']
        ];
        $subComment->selfBlameableRules = $rules;
        $this->assertEquals($rules, $subComment->selfBlameableRules);
        $this->assertEquals(1, count($comment->getChildren()));
        $this->assertTrue($user->deregister());
    }
    
    /**
     * 测试级联删除
     * 正常情况下，本条评论删除后，此后的评论都会删除。
     * 为了防止产生数据一致性问题，删除操作应当从子孙开始。
     * @group comment
     * @depends testNew
     */
    public function testDeleteParentCascade()
    {
        $user = $this->prepareUser();
        $comment = $this->prepareComment($user);
        $subComment = $this->prepareSubComment($comment);
        $comment->save();
        $subComment->save();
        if ($comment->delete()) { // 如果成功删除
            //$query = UserComment::find()->id($subComment->id)->createdBy($user);
            //$copy = clone $query;
            //var_dump($copy->createCommand()->getRawSql());
            // 则找不到下一级评论。
            $sub = UserComment::find()->id($subComment->id)->createdBy($user)->one();
            $this->assertNull($sub);
        } else { // 否则判定为失败。
            var_dump($comment->errors);
            $this->fail();
        }
        $creatorGuid = $user->GUID;
        $this->assertTrue($user->deregister());
        // 账户注销后，所有所属评论都应当删除。
        $count = UserComment::find()->createdBy($creatorGuid)->count();
        $this->assertEquals(0, $count);
    }
    
    /**
     * 测试限制删除。
     * 正常情况下，如果有下级评论（孩子）则不允许删除。
     * @group comment
     * @depends testDeleteParentCascade
     */
    public function testDeleteParentRestrict()
    {
        $user = $this->prepareUser();
        $comment = $this->prepareComment($user);
        $comment->onDeleteType = UserComment::$onRestrict;
        $comment->throwRestrictException = true;
        $subComment = $this->prepareSubComment($comment);
        $subComment->onDeleteType = UserComment::$onRestrict;
        $subComment->throwRestrictException = true;
        $this->assertTrue($comment->save());
        $this->assertTrue($subComment->save());
        try {
            // 执行后应当抛出异常，否则判定为失败。
            $result = $comment->delete();
            $this->fail();
        } catch (\yii\db\IntegrityException $ex) {
            $this->assertEquals('Delete restricted.', $ex->getMessage());
        }
        $sub = UserComment::find()->id($subComment->id)->createdBy($user)->one();
        $this->assertInstanceOf(UserComment::className(), $sub);
        $this->assertTrue($user->deregister());
        $user = $this->prepareUser();
        $comment = $this->prepareComment($user);
        $comment->onDeleteType = UserComment::$onRestrict;
        $subComment = $this->prepareSubComment($comment);
        $subComment->onDeleteType = UserComment::$onRestrict;
        $this->assertTrue($comment->save());
        $this->assertTrue($subComment->save());
        if ($comment->delete()) {
            // 如果能成功删除则判定为失败。
            $this->fail();
        }
        $sub = UserComment::find()->id($subComment->id)->createdBy($user)->one();
        $this->assertInstanceOf(UserComment::className(), $sub);
        $creatorGuid = $user->guid;
        $this->assertTrue($user->deregister());
        // 账户注销后，所有所属评论都应当删除。
        $count = UserComment::find()->createdBy($creatorGuid)->count();
        $this->assertEquals(0, $count);
    }
    
    /**
     * 测试删除后不采取任何动作。
     * @group comment
     * @depends testDeleteParentRestrict
     */
    public function testDeleteParentNoAction()
    {
        $user = $this->prepareUser();
        $comment = $this->prepareComment($user);
        $comment->onDeleteType = UserComment::$onNoAction;
        $subComment = $this->prepareSubComment($comment);
        $subComment->onDeleteType = UserComment::$onNoAction;
        $comment->save();
        $subComment->save();
        if ($comment->delete()) {
            $this->assertTrue(true);
        } else {
            var_dump($comment->errors);
            $this->fail();
        }
        $sub = UserComment::find()->id($subComment->id)->createdBy($user)->one();
        $this->assertInstanceOf(UserComment::className(), $sub);
        $parentAttribute = $comment->parentAttribute;
        $this->assertEquals($subComment->$parentAttribute, $sub->$parentAttribute);
        $creatorGuid = $user->guid;
        $this->assertTrue($user->deregister());
        // 账户注销后，所有所属评论都应当删除。
        $count = UserComment::find()->createdBy($creatorGuid)->count();
        $this->assertEquals(0, $count);
    }
    
    /**
     * @group comment
     * @depends testDeleteParentNoAction
     */
    public function testDeleteParentSetNull()
    {
        $user = $this->prepareUser();
        $comment = $this->prepareComment($user);
        $comment->onDeleteType = UserComment::$onSetNull;
        $subComment = $this->prepareSubComment($comment);
        $subComment->onDeleteType = UserComment::$onSetNull;
        $comment->save();
        $subComment->save();
        if ($comment->delete()) {
            $this->assertTrue(true);
        } else {
            var_dump($comment->errors);
            $this->fail();
        }
        $sub = UserComment::find()->id($subComment->id)->createdBy($user)->one();
        $this->assertInstanceOf(UserComment::className(), $sub);
        $parentAttribute = $comment->parentAttribute;
        $this->assertEquals('', $sub->$parentAttribute);
        $creatorGuid = $user->guid;
        $this->assertTrue($user->deregister());
        // 账户注销后，所有所属评论都应当删除。
        $count = UserComment::find()->createdBy($creatorGuid)->count();
        $this->assertEquals(0, $count);
    }
    
    /**
     * @group comment
     * @depends testDeleteParentSetNull
     */
    public function testUpdateParentCascade()
    {
        $user = $this->prepareUser();
        $comment = $this->prepareComment($user);
        $subComment = $this->prepareSubComment($comment);
        $comment->save();
        $subComment->save();
        $comment->guid = UserComment::GenerateGuid();
        $this->assertTrue($comment->save());
        $sub = UserComment::find()->id($subComment->id)->createdBy($user)->one();
        $this->assertInstanceOf(UserComment::className(), $sub);
        $parentAttribute = $comment->parentAttribute;
        $this->assertEquals($comment->guid, $sub->$parentAttribute);
        $creatorGuid = $user->guid;
        $this->assertTrue($user->deregister());
        // 账户注销后，所有所属评论都应当删除。
        $count = UserComment::find()->createdBy($creatorGuid)->count();
        $this->assertEquals(0, $count);
    }
    
    /**
     * @group comment
     * @depends testUpdateParentCascade
     */
    public function testUpdateParentRestrict()
    {
        $user = $this->prepareUser();
        $comment = $this->prepareComment($user);
        $comment->onUpdateType = UserComment::$onRestrict;
        $comment->throwRestrictException = true;
        $subComment = $this->prepareSubComment($comment);
        $subComment->onUpdateType = UserComment::$onRestrict;
        $subComment->throwRestrictException = true;
        $comment->save();
        $subComment->save();
        $comment->guid = UserComment::GenerateGuid();
        try {
            $result = $comment->save();
            $this->fail();
        } catch (\yii\db\IntegrityException $ex) {
            $this->assertEquals('Update restricted.', $ex->getMessage());
        }
        $sub = UserComment::find()->id($subComment->id)->createdBy($user)->one();
        $this->assertInstanceOf(UserComment::className(), $sub);
        $parentAttribute = $comment->parentAttribute;
        $this->assertEquals($comment->getOldAttribute($comment->guidAttribute), $sub->$parentAttribute);
        $creatorGuid = $user->guid;
        $this->assertTrue($user->deregister());
        // 账户注销后，所有所属评论都应当删除。
        $count = UserComment::find()->createdBy($creatorGuid)->count();
        $this->assertEquals(0, $count);
    }
    
    /**
     * @group comment
     * @depends testUpdateParentRestrict
     */
    public function testUpdateParentNoAction()
    {
        $user = $this->prepareUser();
        $comment = $this->prepareComment($user);
        $comment->onUpdateType = UserComment::$onNoAction;
        $subComment = $this->prepareSubComment($comment);
        $subComment->onUpdateType = UserComment::$onNoAction;
        $comment->save();
        $subComment->save();
        $guid = $comment->guid;
        $comment->guid = UserComment::GenerateGuid();
        $comment->save();
        $sub = UserComment::find()->id($subComment->id)->createdBy($user)->one();
        $this->assertInstanceOf(UserComment::className(), $sub);
        $parentAttribute = $comment->parentAttribute;
        $this->assertEquals($guid, $sub->$parentAttribute);
        $creatorGuid = $user->guid;
        $this->assertTrue($user->deregister());
        // 账户注销后，所有所属评论都应当删除。
        $count = UserComment::find()->createdBy($creatorGuid)->count();
        $this->assertEquals(0, $count);
    }
    
    /**
     * @group comment
     * @depends testUpdateParentNoAction
     */
    public function testUpdateParentSetNull()
    {
        $user = $this->prepareUser();
        $comment = $this->prepareComment($user);
        $comment->onUpdateType = UserComment::$onSetNull;
        $subComment = $this->prepareSubComment($comment);
        $subComment->onUpdateType = UserComment::$onSetNull;
        $comment->save();
        $subComment->save();
        $comment->guid = UserComment::GenerateGuid();
        $comment->save();
        $sub = UserComment::find()->id($subComment->id)->createdBy($user)->one();
        $this->assertInstanceOf(UserComment::className(), $sub);
        $parentAttribute = $comment->parentAttribute;
        $this->assertEquals('', $sub->$parentAttribute);
        $creatorGuid = $user->guid;
        $this->assertTrue($user->deregister());
        // 账户注销后，所有所属评论都应当删除。
        $count = UserComment::find()->createdBy($creatorGuid)->count();
        $this->assertEquals(0, $count);
    }
    
    /**
     * @group comment
     * @depends testUpdateParentSetNull
     */
    public function testAncestor()
    {
        $user = $this->prepareUser();
        $comment = $this->prepareComment($user);
        $comment->onUpdateType = UserComment::$onRestrict;
        $this->assertTrue($comment->save());
        $comments = [];
        $comments[] = $this->prepareSubComment($comment);
        $comments[0]->onUpdateType = UserComment::$onRestrict;
        $this->assertTrue($comments[0]->save());
        for ($i = 1; $i < 10; $i++) {
            $comments[] = $this->prepareSubComment($comments[$i - 1]);
            $comments[$i]->onUpdateType = UserComment::$onRestrict;
            $this->assertTrue($comments[$i]->save());
        }
        $this->assertEquals(10, count($comments));
        $ancestor = $comments[9]->getAncestorChain();
        // $comment 和 $comments[0 - 8] 一共是 10 个。
        $this->assertEquals(10, count($ancestor));
        
        // 指定空数组或不存在的 GUID 时返回 null。
        $this->assertNull(UserComment::getAncestorModels([]));
        $this->assertNull(UserComment::getAncestorModels('ancestor'));
        $ancestorModels = UserComment::getAncestorModels($ancestor);
        $this->assertEquals(10, count($ancestorModels));
        $ancestorModels = $comments[9]->getAncestors();
        $this->assertEquals(10, count($ancestorModels));
        // The order of $ancestorModel should be same as that of $ancestor.
        for ($i = 0; $i < 9; $i++) {
            $this->assertEquals($ancestor[$i], $ancestorModels[$i]->guid);
        }
        $commonComment = $this->prepareSubComment($comments[5]);
        $commonComment->onUpdateType = UserComment::$onRestrict;
        $this->assertTrue($commonComment->save());
        $subCommonComment = $this->prepareSubComment($commonComment);
        $subCommonComment->onUpdateType = UserComment::$onRestrict;
        $this->assertTrue($subCommonComment->save());
        
        // $comments[9] 和 $subCommonComment 应该有共同的祖先。
        $this->assertTrue($comments[9]->hasCommonAncestor($subCommonComment));
        
        // $comments[9] 和 $subCommonComment 共同的祖先应该是 $comments[5]
        $this->assertEquals($comments[5]->guid, $comments[9]->getCommonAncestor($subCommonComment)->guid);
        
        // 自己和自己的共同祖先应该是自己的父结点。
        $this->assertEquals($comments[8]->guid, $comments[9]->getCommonAncestor($comments[9])->guid);
        
        // $comments[9] 和 $comments[5] 共同的节点应该是 $comments[4]
        $this->assertEquals($comments[4]->guid, $comments[9]->getCommonAncestor($comments[5])->guid);
        
        // 根节点没有祖先。
        $this->assertFalse($comment->hasCommonAncestor($comment));
        
        // $subCommonComment 的祖先改为 $comments[5]
        $subCommonComment->parent = $comments[5];
        $this->assertTrue($subCommonComment->save());
        
        // 如果能够保存成功，则 $comments[5] 就是 $subCommonComment 的祖先（以 GUID 为准）。
        $this->assertEquals($comments[5]->guid, $subCommonComment->parent->guid);
        
        // 没有达到祖先数量上限
        $this->assertFalse($subCommonComment->hasReachedAncestorLimit());
        
        // 也没有达到孩子数量上限。
        $this->assertFalse($subCommonComment->hasReachedChildrenLimit());
        $this->assertFalse($comment->hasReachedChildrenLimit());
        $this->assertFalse($comments[9]->hasReachedAncestorLimit());
        $this->assertFalse($comments[9]->hasReachedChildrenLimit());
        $this->assertFalse($comments[5]->hasReachedAncestorLimit());
        $this->assertFalse($comments[5]->hasReachedChildrenLimit());
        $creatorGuid = $user->guid;
        $this->assertTrue($user->deregister());
        // 账户注销后，所有所属评论都应当删除。
        $count = UserComment::find()->createdBy($creatorGuid)->count();
        $this->assertEquals(0, $count);
    }
    
    /**
     * @group comment
     * @depends testAncestor
     */
    public function testAncestorEvents()
    {
        $user = $this->prepareUser();
        $comment = $this->prepareComment($user);
        $this->assertTrue($comment->save());
        $creatorGuid = $user->guid;
        $this->assertTrue($user->deregister());
        // 账户注销后，所有所属评论都应当删除。
        $count = UserComment::find()->createdBy($creatorGuid)->count();
        $this->assertEquals(0, $count);
    }
}