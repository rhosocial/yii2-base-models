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

namespace rhosocial\base\models\tests\user\blameable;

use rhosocial\base\models\tests\data\ar\blameable\UserComment;
use rhosocial\base\models\tests\data\ar\blameable\UserPost;

/**
 * @author vistart <i@vistart.me>
 */
class CommentTest extends BlameableTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->assertTrue($this->user->register(array_merge([$this->post], $this->comments)));
        $this->assertTrue($this->other->register());
        $this->post->refresh();
        for ($i = 0; $i < 10; $i++) {
            $this->comments[$i]->refresh();
        }
    }
    
    protected function tearDown()
    {
        foreach ($this->comments as $comment) {
            $comment->delete();
        }
        $this->post->delete();
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
        parent::tearDown();
    }
    
    /**
     * @group blameable
     * @group post
     * @group comment
     */
    public function testLocalRules()
    {
        $this->assertNotEmpty($this->comments[0]->getSelfBlameableRules());
    }
    
    /**
     * @group blameable
     * @group post
     * @group comment
     */
    public function testSetLocalRules()
    {
        $rules = $this->comments[0]->getSelfBlameableRules();
        $this->comments[0]->setSelfBlameableRules($rules);
        $this->assertEquals($rules, $this->comments[0]->getSelfBlameableRules());
    }
    
    /**
     * @group blameable
     * @group post
     * @group comment
     */
    public function testSetEmptyParent()
    {
        $this->assertFalse($this->comments[9]->setParent(null));
        $this->assertFalse($this->comments[9]->setParent($this->comments[9]));
        for ($i = 0; $i < 9; $i++) {
            for ($j = $i + 1; $j < 10; $j++) {
                $this->assertFalse($this->comments[$i]->setParent($this->comments[$j]));
            }
        }
        $this->comments[9]->ancestorLimit = 1;
        $this->assertFalse($this->comments[9]->setParent($this->comments[8]));
    }
    
    /**
     * @group blameable
     * @group post
     * @group comment
     */
    public function testNotReachAncestorLimit()
    {
        $this->assertFalse($this->comments[9]->hasReachedAncestorLimit());
    }
    
    /**
     * @group blameable
     * @group post
     * @group comment
     */
    public function testNonNumericAncestorLimit()
    {
        $this->comments[9]->ancestorLimit = 0; // Disable ancestor.
        $this->assertTrue($this->comments[9]->hasReachedAncestorLimit());
        $this->assertEquals(0, $this->comments[9]->ancestorLimit);
        
        $this->comments[9]->ancestorLimit = -1;
        $this->assertFalse($this->comments[9]->hasReachedAncestorLimit());
        $this->assertEquals(256, $this->comments[9]->ancestorLimit);
        
        $this->comments[9]->ancestorLimit = null;
        $this->assertFalse($this->comments[9]->hasReachedAncestorLimit());
        $this->assertEquals(256, $this->comments[9]->ancestorLimit);
    }
    
    /**
     * @group blameable
     * @group post
     * @group comment
     */
    public function testReachAncestorLimit()
    {
        for ($i = 1; $i < 10; $i++) {
            $this->comments[$i]->ancestorLimit = $i;
            $this->assertTrue($this->comments[$i]->hasReachedAncestorLimit());
            $this->comments[$i]->ancestorLimit = $i + 1;
            $this->assertFalse($this->comments[$i]->hasReachedAncestorLimit());
        }
    }
    
    /**
     * @group blameable
     * @group post
     * @group comment
     */
    public function testReachChildrenLimit()
    {
        for ($i = 0; $i < 9; $i++) {
            $this->assertFalse($this->comments[$i]->hasReachedChildrenLimit());
            
            $this->comments[$i]->childrenLimit = 0;
            $this->assertTrue($this->comments[$i]->hasReachedChildrenLimit());
            
            $this->comments[$i]->childrenLimit = 1;
            $this->assertTrue($this->comments[$i]->hasReachedChildrenLimit());
            
            $this->comments[$i]->childrenLimit = 2;
            $this->assertFalse($this->comments[$i]->hasReachedChildrenLimit());
            
            $this->comments[$i]->childrenLimit = null;
            $this->assertFalse($this->comments[$i]->hasReachedChildrenLimit());
            $this->assertEquals(1024, $this->comments[$i]->childrenLimit);
            
            $this->comments[$i]->childrenLimit = -1;
            $this->assertFalse($this->comments[$i]->hasReachedChildrenLimit());
            $this->assertEquals(1024, $this->comments[$i]->childrenLimit);
        }
    }
    
    /**
     * @group blameable
     * @group post
     * @group comment
     */
    public function testGetAncestorModels()
    {
        $this->assertEquals([], UserComment::getAncestorModels([]));
        $ancestors = UserComment::getAncestorModels($this->comments[9]->getAncestorChain());
        for ($i = 0; $i < 9; $i++) {
            $this->assertEquals($this->comments[8 - $i]->getGUID(), $ancestors[$i]->getGUID());
        }
    }
    
    /**
     * @group blameable
     * @group post
     * @group comment
     */
    public function testGetAncestors()
    {
        $ancestors = $this->comments[9]->ancestors;
        for ($i = 0; $i < 9; $i++) {
            $this->assertEquals($this->comments[8 - $i]->getGUID(), $ancestors[$i]->getGUID());
        }
    }
    
    /**
     * @group blameable
     * @group post
     * @group comment
     */
    public function testCommitComment()
    {
        foreach ($this->comments as $comment) {
            /* @var $comment UserComment */
            $content = \Yii::$app->security->generateRandomString();
            $sub = UserComment::commit($comment, $content, $this->other);
            $this->assertInstanceOf(UserComment::class, $sub);
            $this->assertFalse($sub->getIsNewRecord());
            $this->assertTrue($sub->user->equals($this->other));
            $this->assertTrue($sub->parent->equals($comment));
            $this->assertEquals(1, $sub->delete());
        }
    }
    
    /**
     * @group blameable
     * @group post
     * @group comment
     */
    public function testChildren()
    {
        for ($i = 0; $i < 9; $i++)
        {
            $children = $this->comments[$i]->children;
            $this->assertCount(1, $children);
            $this->assertTrue($children[0]->parent->equals($this->comments[$i]));
        }
    }
    
    /**
     * @group blameable
     * @group post
     * @group comment
     */
    public function testHasCommonAncestor()
    {
        for ($i = 1; $i < 10; $i++) {
            $content = \Yii::$app->security->generateRandomString();
            $sub = UserComment::commit($this->comments[$i], $content, $this->other);
            $this->assertTrue($sub->hasCommonAncestor($this->comments[$i]));
            $this->assertTrue($sub->getCommonAncestor($this->comments[$i])->equals($this->comments[$i - 1]));
            $this->assertEquals(1, $sub->delete());
        }
    }
    
    /**
     * @group blameable
     * @group post
     * @group comment
     */
    public function testGetCommonAncestor()
    {
        $sub = UserComment::commit($this->comments[0], \Yii::$app->security->generateRandomString(), $this->other);
        for ($i = 1; $i < 10; $i++) {
            $content = \Yii::$app->security->generateRandomString();
            $sub = UserComment::commit($sub, $content, $this->other);
        }
        $this->assertTrue($sub->hasCommonAncestor($this->comments[9]));
        $this->assertTrue($sub->getCommonAncestor($this->comments[9])->equals($this->comments[0]));
    }

    /**
     * @group blameable
     * @group post
     * @group comment
     */   
    public function testPost()
    {
        foreach ($this->comments as $comment) {
            /* @var $comment UserComment */
            $this->assertInstanceOf(UserPost::class, $comment->post);
        }
    }

    /**
     * @group blameable
     * @group post
     * @group comment
     */   
    public function testParent()
    {
        for ($i = 1; $i < 10; $i++) {
            $this->assertInstanceOf(UserComment::class, $this->comments[$i]->parent);
            $this->assertTrue($this->comments[$i]->parent->equals($this->comments[$i - 1]));
        }
     }
    
    /**
     * @group blameable
     * @group post
     * @group comment
     */
    public function testAncestor()
    {
        for ($i = 0; $i < 9; $i++) {
            for ($j = $i + 1; $j < 10; $j++) {
                $comment = $this->comments[$j];
                /* @var $comment UserComment */
                $this->assertTrue($comment->hasAncestor($this->comments[$i]), "$i is not the ancestor of $j.");
            }
        }
    }
    
    /**
     * @group blameable
     * @group post
     * @group comment
     */
    public function testDeleteCascade()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->assertInstanceOf(UserComment::class, UserComment::find()->id($this->comments[$i]->getID())->createdBy($this->user)->one());
        }
        for ($i = 0; $i < 9; $i++) {
            $this->assertCount(1, $this->comments[$i]->children);
            $this->assertTrue($this->comments[$i]->children[0]->equals($this->comments[$i + 1]));
            $this->assertTrue($this->comments[$i + 1]->parent->equals($this->comments[$i]));
        }
        
        $this->assertEquals(1, $this->comments[5]->delete());
        for ($i = 0; $i < 10; $i++) {
            $this->comments[$i]->refresh();
        }
        for ($i = 0; $i < 5; $i++) {
            $this->assertInstanceOf(UserComment::class, UserComment::find()->id($this->comments[$i]->getID())->createdBy($this->user)->one());
        }
        for ($i = 5; $i < 10; $i++) {
            $this->assertNull(UserComment::find()->id($this->comments[$i]->getID())->createdBy($this->user)->one());
        }
    }
    
    /**
     * @group blameable
     * @group post
     * @group comment
     */
    public function testDeleteRestrict()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->assertInstanceOf(UserComment::class, UserComment::find()->id($this->comments[$i]->getID())->createdBy($this->user)->one());
        }
        for ($i = 0; $i < 9; $i++) {
            $this->assertCount(1, $this->comments[$i]->children);
            $this->assertTrue($this->comments[$i]->children[0]->equals($this->comments[$i + 1]));
            $this->assertTrue($this->comments[$i + 1]->parent->equals($this->comments[$i]));
        }
        
        $this->comments[5]->onDeleteType = UserComment::$onRestrict;
        $this->comments[5]->throwRestrictException = true;
        try {
            $this->comments[5]->delete();
            $this->fail();
        } catch (\Exception $ex) {
            $this->assertEquals('Delete restricted.', $ex->getMessage());
        }
        for ($i = 0; $i < 10; $i++) {
            $this->comments[$i]->refresh();
        }
        for ($i = 0; $i < 10; $i++) {
            $this->assertInstanceOf(UserComment::class, UserComment::find()->id($this->comments[$i]->getID())->createdBy($this->user)->one());
        }
        
        $this->comments[5]->throwRestrictException = false;
        try {
            $this->assertEquals(0, $this->comments[5]->delete());
        } catch (\Exception $ex) {
            $this->fail($ex->getMessage());
        }
        for ($i = 0; $i < 10; $i++) {
            $this->comments[$i]->refresh();
        }
        for ($i = 0; $i < 10; $i++) {
            $this->assertInstanceOf(UserComment::class, UserComment::find()->id($this->comments[$i]->getID())->createdBy($this->user)->one());
        }
    }
    
    /**
     * @group blameable
     * @group post
     * @group comment
     */
    public function testDeleteSetNull()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->assertInstanceOf(UserComment::class, UserComment::find()->id($this->comments[$i]->getID())->createdBy($this->user)->one());
        }
        for ($i = 0; $i < 9; $i++) {
            $this->assertCount(1, $this->comments[$i]->children);
            $this->assertTrue($this->comments[$i]->children[0]->equals($this->comments[$i + 1]));
            $this->assertTrue($this->comments[$i + 1]->parent->equals($this->comments[$i]));
        }
        
        $this->comments[5]->onDeleteType = UserComment::$onSetNull;
        $this->assertEquals(1, $this->comments[5]->delete());
        
        for ($i = 0; $i < 10; $i++) {
            $this->comments[$i]->refresh();
        }
        for ($i = 0; $i < 10; $i++) {
            if ($i != 5) {
                $comment = UserComment::find()->id($this->comments[$i]->getID())->createdBy($this->user)->one();
                $this->assertInstanceOf(UserComment::class, $comment);
            } else {
                $this->assertNull(UserComment::find()->id($this->comments[$i]->getID())->createdBy($this->user)->one());
            }
        }
        for ($i = 0; $i < 4; $i++) {
            $this->assertCount(1, $this->comments[$i]->children, "Comment[$i] has " . count($this->comments[$i]->children) . " child(ren)");
            $this->assertTrue($this->comments[$i]->children[0]->equals($this->comments[$i + 1]));
            $this->assertTrue($this->comments[$i + 1]->parent->equals($this->comments[$i]));
        }
        $this->assertCount(0, $this->comments[4]->children, "Comment[4] has " . count($this->comments[4]->children) . " child(ren)");
        $this->assertTrue($this->comments[5]->getIsNewRecord());
        $this->assertFalse($this->comments[6]->hasParent());
        $this->assertEquals(UserComment::$nullParent, $this->comments[6]->getParentId());
        for ($i = 6; $i < 9; $i++) {
            $this->assertTrue($this->comments[$i + 1]->hasParent());
            $this->assertTrue($this->comments[$i]->children[0]->equals($this->comments[$i + 1]));
        }
        
        $this->assertFalse($this->comments[6]->clearInvalidParent());
        $this->assertTrue($this->comments[6]->save());
    }
    
    /**
     * @group blameable
     * @group post
     * @group comment
     */
    public function testDeleteNoAction()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->assertInstanceOf(UserComment::class, UserComment::find()->id($this->comments[$i]->getID())->createdBy($this->user)->one());
        }
        for ($i = 0; $i < 9; $i++) {
            $this->assertCount(1, $this->comments[$i]->children);
            $this->assertTrue($this->comments[$i]->children[0]->equals($this->comments[$i + 1]));
            $this->assertTrue($this->comments[$i + 1]->parent->equals($this->comments[$i]));
        }
        
        $this->comments[5]->onDeleteType = UserComment::$onNoAction;
        $this->assertEquals(1, $this->comments[5]->delete());
        
        for ($i = 0; $i < 10; $i++) {
            $this->comments[$i]->refresh();
        }
        for ($i = 0; $i < 10; $i++) {
            if ($i != 5) {
                $comment = UserComment::find()->id($this->comments[$i]->getID())->createdBy($this->user)->one();
                $this->assertInstanceOf(UserComment::class, $comment);
            } else {
                $this->assertNull(UserComment::find()->id($this->comments[$i]->getID())->createdBy($this->user)->one());
            }
        }
        for ($i = 0; $i < 4; $i++) {
            $this->assertCount(1, $this->comments[$i]->children, "Comment[$i] has " . count($this->comments[$i]->children) . " child(ren)");
            $this->assertTrue($this->comments[$i]->children[0]->equals($this->comments[$i + 1]));
            $this->assertTrue($this->comments[$i + 1]->parent->equals($this->comments[$i]));
        }
        $this->assertCount(0, $this->comments[4]->children, "Comment[4] has " . count($this->comments[4]->children) . " child(ren)");
        $this->assertTrue($this->comments[5]->getIsNewRecord());
        $this->assertFalse($this->comments[6]->hasParent());
        $this->assertEquals($this->comments[5]->getRefId(), $this->comments[6]->getParentId());
        for ($i = 6; $i < 9; $i++) {
            $this->assertTrue($this->comments[$i + 1]->hasParent());
            $this->assertTrue($this->comments[$i]->children[0]->equals($this->comments[$i + 1]));
        }
        
        $this->assertTrue($this->comments[6]->clearInvalidParent());
        $this->assertTrue($this->comments[6]->save());
    }
    
    /**
     * @group blameable
     * @group post
     * @group comment
     */
    public function testUpdateCascade()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->assertInstanceOf(UserComment::class, UserComment::find()->id($this->comments[$i]->getID())->createdBy($this->user)->one());
        }
        for ($i = 0; $i < 9; $i++) {
            $this->assertCount(1, $this->comments[$i]->children);
            $this->assertTrue($this->comments[$i]->children[0]->equals($this->comments[$i + 1]));
            $this->assertTrue($this->comments[$i + 1]->parent->equals($this->comments[$i]));
        }
        
        $newGUID = UserComment::generateGuid();
        $this->assertNotEquals($newGUID, $this->comments[5]->getGUID());
        $this->comments[5]->setGUID($newGUID);
        $this->assertTrue($this->comments[5]->save());
        
        for ($i = 0; $i < 10; $i++) {
            $this->comments[$i]->refresh();
        }
        
        $this->assertEquals($newGUID, $this->comments[5]->getGUID());
        for ($i = 0; $i < 10; $i++) {
            $this->assertInstanceOf(UserComment::class, UserComment::find()->id($this->comments[$i]->getID())->createdBy($this->user)->one());
        }
        for ($i = 0; $i < 9; $i++) {
            $this->assertCount(1, $this->comments[$i]->children);
            $this->assertTrue($this->comments[$i]->children[0]->equals($this->comments[$i + 1]));
            $this->assertTrue($this->comments[$i + 1]->parent->equals($this->comments[$i]));
        }
    }
    
    /**
     * @group blameable
     * @group post
     * @group comment
     */
    public function testUpdateRestrict()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->assertInstanceOf(UserComment::class, UserComment::find()->id($this->comments[$i]->getID())->createdBy($this->user)->one());
        }
        for ($i = 0; $i < 9; $i++) {
            $this->assertCount(1, $this->comments[$i]->children);
            $this->assertTrue($this->comments[$i]->children[0]->equals($this->comments[$i + 1]));
            $this->assertTrue($this->comments[$i + 1]->parent->equals($this->comments[$i]));
        }
        
        $this->comments[5]->onUpdateType = UserComment::$onRestrict;
        $this->comments[5]->throwRestrictException = true;
        
        $newGUID = UserComment::generateGuid();
        $this->assertNotEquals($newGUID, $this->comments[5]->getGUID());
        $this->comments[5]->setGUID($newGUID);
        try {
            $this->comments[5]->save();
            $this->fail();
        } catch (\Exception $ex) {
            $this->assertEquals('Update restricted.', $ex->getMessage());
        }
        
        for ($i = 0; $i < 10; $i++) {
            $this->comments[$i]->refresh();
        }
        for ($i = 0; $i < 10; $i++) {
            $this->assertInstanceOf(UserComment::class, UserComment::find()->id($this->comments[$i]->getID())->createdBy($this->user)->one());
        }
        
        $this->comments[5]->throwRestrictException = false;
        
        $newGUID = UserComment::generateGuid();
        $this->assertNotEquals($newGUID, $this->comments[5]->getGUID());
        $this->comments[5]->setGUID($newGUID);
        $this->assertFalse($this->comments[5]->save());
        
        for ($i = 0; $i < 10; $i++) {
            $this->comments[$i]->refresh();
        }
        for ($i = 0; $i < 10; $i++) {
            $this->assertInstanceOf(UserComment::class, UserComment::find()->id($this->comments[$i]->getID())->createdBy($this->user)->one());
        }
    }
    
    /**
     * 更新后将所有子节点的父亲属性设为空字符串（UserComment::$nullParent）。
     * 此测试重置第 5 节点（从 0 开始）的 GUID 值。
     * 
     * 重置 GUID 前，十个节点彼此构成父子链条。
     * 重置 GUID 后，
     * 1. 第 5 节点的 GUID 值应当与重置前不一样;
     * 2. 第 6 节点的父亲属性为空字符串（UserComment::$nullParent）。
     * 3. 第 5 节点依然不是新记录。
     * 4. 第 5 节点的父亲依然存在。
     * @group blameable
     * @group post
     * @group comment
     */
    public function testUpdateSetNull()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->assertInstanceOf(UserComment::class, UserComment::find()->id($this->comments[$i]->getID())->createdBy($this->user)->one());
        }
        for ($i = 0; $i < 9; $i++) {
            $this->assertCount(1, $this->comments[$i]->children);
            $this->assertTrue($this->comments[$i]->children[0]->equals($this->comments[$i + 1]));
            $this->assertTrue($this->comments[$i + 1]->parent->equals($this->comments[$i]));
        }
        
        $this->comments[5]->onUpdateType = UserComment::$onSetNull;
        $newGUID = UserComment::generateGuid();
        $this->assertNotEquals($newGUID, $this->comments[5]->getGUID());
        $this->comments[5]->setGUID($newGUID);
        $this->assertTrue($this->comments[5]->save());
        
        // Refresh after updating.
        for ($i = 0; $i < 10; $i++) {
            $this->comments[$i]->refresh();
        }
        for ($i = 0; $i < 10; $i++) {
            $comment = UserComment::find()->id($this->comments[$i]->getID())->createdBy($this->user)->one();
            $this->assertInstanceOf(UserComment::class, $comment);
        }
        for ($i = 0; $i < 5; $i++) {
            $this->assertCount(1, $this->comments[$i]->children, "Comment[$i] has " . count($this->comments[$i]->children) . " child(ren)");
            $this->assertTrue($this->comments[$i]->children[0]->equals($this->comments[$i + 1]));
            $this->assertTrue($this->comments[$i + 1]->parent->equals($this->comments[$i]));
        }
        $this->assertFalse($this->comments[5]->getIsNewRecord());
        $this->assertFalse($this->comments[6]->hasParent());
        $this->assertEquals(UserComment::$nullParent, $this->comments[6]->getParentId());
        for ($i = 6; $i < 9; $i++) {
            $this->assertTrue($this->comments[$i + 1]->hasParent());
            $this->assertTrue($this->comments[$i]->children[0]->equals($this->comments[$i + 1]));
        }
        
        $this->assertFalse($this->comments[6]->clearInvalidParent());
        $this->assertTrue($this->comments[6]->save());
    }
    
    /**
     * @group blameable
     * @group post
     * @group comment
     */
    public function testUpdateNoAction()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->assertInstanceOf(UserComment::class, UserComment::find()->id($this->comments[$i]->getID())->createdBy($this->user)->one());
        }
        for ($i = 0; $i < 9; $i++) {
            $this->assertCount(1, $this->comments[$i]->children);
            $this->assertTrue($this->comments[$i]->children[0]->equals($this->comments[$i + 1]));
            $this->assertTrue($this->comments[$i + 1]->parent->equals($this->comments[$i]));
        }
        
        $this->comments[5]->onUpdateType = UserComment::$onNoAction;
        $oldGUID = $this->comments[5]->getRefId();
        $newGUID = UserComment::generateGuid();
        $this->assertNotEquals($newGUID, $this->comments[5]->getGUID());
        $this->comments[5]->setGUID($newGUID);
        $this->assertTrue($this->comments[5]->save());
        
        // Refresh after updating.
        for ($i = 0; $i < 10; $i++) {
            $this->comments[$i]->refresh();
        }
        for ($i = 0; $i < 10; $i++) {
            $comment = UserComment::find()->id($this->comments[$i]->getID())->createdBy($this->user)->one();
            $this->assertInstanceOf(UserComment::class, $comment);
        }
        for ($i = 0; $i < 5; $i++) {
            $this->assertCount(1, $this->comments[$i]->children, "Comment[$i] has " . count($this->comments[$i]->children) . " child(ren)");
            $this->assertTrue($this->comments[$i]->children[0]->equals($this->comments[$i + 1]));
            $this->assertTrue($this->comments[$i + 1]->parent->equals($this->comments[$i]));
        }
        $this->assertFalse($this->comments[5]->getIsNewRecord());
        $this->assertFalse($this->comments[6]->hasParent());
        $this->assertEquals($newGUID, $this->comments[5]->getRefId());
        $this->assertEquals($oldGUID, $this->comments[6]->getParentId());
        for ($i = 6; $i < 9; $i++) {
            $this->assertTrue($this->comments[$i + 1]->hasParent());
            $this->assertTrue($this->comments[$i]->children[0]->equals($this->comments[$i + 1]));
        }
        
        $this->assertTrue($this->comments[6]->clearInvalidParent());
        $this->assertTrue($this->comments[6]->save());
    }
    
    /**
     * @group blameable
     * @group post
     * @group comment
     */
    public function testDeleteChildren()
    {
        $content = $this->comments[5]->getContent();
        $comment = UserComment::commit($this->comments[4], \Yii::$app->security->generateRandomString(), $this->user);
        $this->assertCount(2, $this->comments[4]->children);
        UserComment::getDb()->createCommand()->delete('user_comment', ['content' => $content])->execute();
        $this->assertInstanceOf(\yii\db\IntegrityException::class, $this->comments[4]->deleteChildren());
    }
    
    /**
     * @group blameable
     * @group post
     * @group comment
     */
    public function testFindByParent()
    {
        $comment = UserComment::find()->parentGuid($this->comments[4])->one();
        /* @var $comment UserComment */
        $this->assertInstanceOf(UserComment::class, $comment);
        $this->assertEquals($comment->{$comment->parentAttribute}, $this->comments[4]->getGUID());
        $this->assertEquals($comment->getReadableGUID(), $this->comments[5]->getReadableGUID());
        
        $comment = UserComment::find()->parentGuid(UserComment::$nullParent)->one();
        /* @var $comment UserComment */
        $this->assertInstanceOf(UserComment::class, $comment);
        $this->assertEquals($comment->{$comment->parentAttribute}, UserComment::$nullParent);
        $this->assertEquals($comment->getReadableGUID(), $this->comments[0]->getReadableGUID());
    }
    
    /**
     * @group blameable
     * @group post
     * @group comment
     */
    public function testBearConfigClass()
    {
        $this->assertInstanceOf(UserComment::class, $this->comments[5]->bear(['class' => User::class, 'content' => \Yii::$app->security->generateRandomString()]));
    }
    
    /**
     * @group blameable
     * @group post
     * @group comment
     */
    public function testBearAncestorLimit()
    {
        $this->comments[5]->ancestorLimit = 6;
        $this->assertInstanceOf(UserComment::class, $this->comments[5]->bear(['content' => \Yii::$app->security->generateRandomString()]));
        
        $this->comments[5]->ancestorLimit = 5;
        try {
            $this->comments[5]->bear(['content' => \Yii::$app->security->generateRandomString()]);
            $this->fail();
        } catch (\Exception $ex) {
            $this->assertInstanceOf(\yii\base\InvalidParamException::class, $ex);
        }
    }
    
    /**
     * @group blameable
     * @group post
     * @group comment
     */
    public function testBearChildrenLimit()
    {
        $this->comments[5]->childrenLimit = 1;
        try {
            $this->comments[5]->bear(['content' => \Yii::$app->security->generateRandomString()]);
            $this->fail();
        } catch (\Exception $ex) {
            $this->assertInstanceOf(\yii\base\InvalidParamException::class, $ex);
        }
    }
    
    /**
     * @group blameable
     * @group post
     * @group comment
     */
    public function testEnabledFields()
    {
        $this->assertNotEmpty($this->comments[0]->enabledFields());
    }
}