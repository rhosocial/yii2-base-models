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
        if ($comment->delete()) {
            $query = UserComment::find()->id($subComment->id)->createdBy($user);
            $copy = clone $query;
            //var_dump($copy->createCommand()->getRawSql());
            $sub = UserComment::find()->id($subComment->id)->createdBy($user)->one();
            $this->assertNull($sub);
        } else {
            var_dump($comment->errors);
            $this->fail();
        }
        $this->assertTrue($user->deregister());
    }
    
    /**
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
        $comment->save();
        $subComment->save();
        try {
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
        $comment->save();
        $subComment->save();
        if ($comment->delete()) {
            $this->fail();
        }
        $sub = UserComment::find()->id($subComment->id)->createdBy($user)->one();
        $this->assertInstanceOf(UserComment::className(), $sub);
        $this->assertTrue($user->deregister());
    }
    /**
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
        $this->assertTrue($user->deregister());
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
        $this->assertTrue($user->deregister());
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
        $this->assertTrue($user->deregister());
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
        $this->assertTrue($user->deregister());
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
        $this->assertTrue($user->deregister());
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
        $this->assertTrue($user->deregister());
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
        $this->assertEquals(10, count($ancestor));
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
        $this->assertTrue($comments[9]->hasCommonAncestor($subCommonComment));
        $this->assertEquals($comments[5]->guid, $comments[9]->getCommonAncestor($subCommonComment)->guid);
        $this->assertEquals($comments[8]->guid, $comments[9]->getCommonAncestor($comments[9])->guid);
        $this->assertEquals($comments[4]->guid, $comments[9]->getCommonAncestor($comments[5])->guid);
        $this->assertFalse($comment->hasCommonAncestor($comment));
        $subCommonComment->parent = $comments[5];
        $this->assertTrue($subCommonComment->save());
        $this->assertEquals($comments[5]->guid, $subCommonComment->parent->guid);
        $this->assertFalse($subCommonComment->hasReachedAncestorLimit());
        $this->assertFalse($subCommonComment->hasReachedDescendantLimit());
        $this->assertFalse($comment->hasReachedDescendantLimit());
        $this->assertFalse($comments[9]->hasReachedAncestorLimit());
        $this->assertFalse($comments[9]->hasReachedDescendantLimit());
        $this->assertFalse($comments[5]->hasReachedAncestorLimit());
        $this->assertFalse($comments[5]->hasReachedDescendantLimit());
        $this->assertTrue($user->deregister());
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
        $this->assertTrue($user->deregister());
    }
}