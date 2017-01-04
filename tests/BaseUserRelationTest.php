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
            $this->assertInstanceOf(UserRelation::className(), $opposite);
            $opposite = $relation->opposite;
            $this->assertInstanceOf(UserRelation::className(), $opposite);
            $opposite = UserRelation::find()->opposite($user, $other);
            $this->assertInstanceOf(UserRelation::className(), $opposite);
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
}