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
}