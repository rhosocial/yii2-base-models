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

namespace rhosocial\base\models\tests\data\ar\blameable;

use rhosocial\base\models\tests\data\ar\User;
use rhosocial\base\models\models\BaseBlameableModel;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class UserComment extends BaseBlameableModel
{
    public int $idAttributeLength = 16;

    public string|false $parentAttribute = 'parent_guid';

    public function __construct($config = array())
    {
        $this->hostClass = User::class;
        parent::__construct($config);
    }

    public function init()
    {
        parent::init();
        $this->setContent(\Yii::$app->security->generateRandomString());
    }

    public static function tableName()
    {
        return '{{%user_comment}}';
    }

    /**
     * Friendly to IDE.
     * @return \rhosocial\base\models\queries\BaseBlameableQuery
     */
    public static function find()
    {
        return parent::find();
    }

    /**
     * 
     * @return UserPost
     */
    public function getPost()
    {
        return UserPost::findOne($this->post_guid);
    }

    /**
     * 
     * @param UserPost $post
     */
    public function setPost($post)
    {
        if ($post instanceof UserPost) {
            $this->post_guid = $post->getGUID();
        } else {
            $this->post_guid = '';
        }
    }

    /**
     * Commit a comment.
     * @param static $comment
     * @param string $content
     * @param User $user
     * @return static
     */
    public static function commit($comment, $content, $user)
    {
        $sub = $comment->bear(['post' => $comment->post, 'user' => $user, 'content' => $content]);
        if (!($sub instanceof static) || !$sub->save()) {
            return false;
        }
        return $sub;
    }
}
