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
        $this->post->refresh();
        for ($i = 0; $i < 10; $i++) {
            $this->comments[$i]->refresh();
        }
    }
    
    protected function tearDown()
    {
        $this->assertTrue($this->user->deregister());
        parent::tearDown();
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