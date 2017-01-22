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

use rhosocial\base\models\tests\data\ar\blameable\UserPost;
use rhosocial\base\models\tests\data\ar\blameable\UserComment;
use rhosocial\base\models\tests\user\UserTestCase;

/**
 * @author vistart <i@vistart.me>
 */
class BlameableTestCase extends UserTestCase
{
    /**
     *
     * @var UserPost
     */
    public $post = null;
    
    /**
     *
     * @var array
     */
    public $comments = null;
    
    protected function setUp() {
        parent::setUp();
        $this->post = $this->user->create(UserPost::class);
        $this->comments = [];
        $this->comments[0] = $this->user->create(UserComment::class, ['post' => $this->post]);
        for ($i = 1; $i < 10; $i++) {
            $this->comments[] = $this->user->create(UserComment::class, ['post' => $this->post, 'parent' => $this->comments[$i - 1]]);
        }
    }
    
    protected function tearDown() {
        UserComment::deleteAll();
        UserPost::deleteAll();
        parent::tearDown();
    }
}