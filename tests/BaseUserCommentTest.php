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
}