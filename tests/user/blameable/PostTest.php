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

namespace rhosocial\base\models\tests\user\blameable;

use rhosocial\base\models\tests\data\ar\User;
use rhosocial\base\models\tests\data\ar\blameable\UserPost;
use rhosocial\base\models\tests\data\ar\blameable\UserComment;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class PostTest extends BlameableTestCase
{
    /**
     * @group blameable
     * @group post
     */
    public function testNew()
    {
        $this->assertTrue($this->user->register(array_merge([$this->post], $this->comments)));
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group blameable
     * @group post
     */
    public function testCreator()
    {
        $this->assertTrue($this->user->register(array_merge([$this->post], $this->comments)));
        $this->assertEquals((string)$this->user, (string)$this->post->user);
        foreach ($this->comments as $c) {
            /* @var $c UserComment */
            $this->assertEquals((string)$this->user, (string)$this->post->user);
        }
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group blameable
     * @group post
     */
    public function testGetUpdater()
    {
        $this->assertTrue($this->user->register(array_merge([$this->post], $this->comments)));
        $this->assertEquals((string)$this->user, (string)$this->post->updater);
        foreach ($this->comments as $c) {
            /* @var $c UserComment */
            $this->assertEquals((string)$this->user, (string)$this->post->updater);
        }
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group blameable
     * @group post
     */
    public function testSetUpdaterInstance()
    {
        $this->assertTrue($this->user->register(array_merge([$this->post], $this->comments)));
        $this->assertTrue($this->other->register());
        
        $this->post->setUpdater($this->other);
        $this->assertInstanceOf(User::class, $this->post->updater);
        $this->assertTrue($this->post->save());
        unset($this->post->updater);
        $this->assertTrue($this->post->updater->equals($this->other));
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group blameable
     * @group post
     */
    public function testSetUpdaterGuid()
    {
        $this->assertTrue($this->user->register(array_merge([$this->post], $this->comments)));
        $this->assertTrue($this->other->register());
        
        $this->post->setUpdater($this->other->getReadableGUID());
        $this->assertInstanceOf(User::class, $this->post->updater);
        $this->assertTrue($this->post->save());
        unset($this->post->updater);
        $this->assertTrue($this->post->updater->equals($this->other));
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group blameable
     * @group post
     */
    public function testSetUpdaterGuidBinary()
    {
        $this->assertTrue($this->user->register(array_merge([$this->post], $this->comments)));
        $this->assertTrue($this->other->register());
        
        $this->post->setUpdater($this->other->getGUID());
        $this->assertInstanceOf(User::class, $this->post->updater);
        $this->assertTrue($this->post->save());
        unset($this->post->updater);
        $this->assertTrue($this->post->updater->equals($this->other));
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group blameable
     * @group post
     */
    public function testSetUpdaterInvalid()
    {
        $this->assertTrue($this->user->register(array_merge([$this->post], $this->comments)));
        $this->assertTrue($this->other->register());
        
        $this->assertFalse($this->post->setUpdater(null));
        $this->assertTrue($this->post->save());
        unset($this->post->updater);
        $this->assertTrue($this->post->updater->equals($this->user));
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group blameable
     * @group post
     */
    public function testHasEverBeenEdited()
    {
        $this->assertTrue($this->user->register(array_merge([$this->post], $this->comments)));
        $this->assertFalse($this->post->hasEverBeenEdited());
        foreach ($this->comments as $c) {
            /* @var $c UserComment */
            $this->assertFalse($c->hasEverBeenEdited());
        }
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group blameable
     * @group post
     */
    public function testEnabledFields()
    {
        $this->assertTrue($this->user->register(array_merge([$this->post], $this->comments)));
        $this->assertNotEmpty($this->post->enabledFields());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group blameable
     * @group post
     */
    public function testFindAllByIdentityInBatch()
    {
        $result = UserPost::findAllByIdentityInBatch();
        $this->assertCount(0, $result);
        $this->assertTrue($this->user->register(array_merge([$this->post], $this->comments)));
        $result = UserPost::findAllByIdentityInBatch();
        $this->assertCount(1, $result);
        $result = UserPost::findAllByIdentityInBatch(1);
        $this->assertCount(1, $result);
        $result = UserPost::findAllByIdentityInBatch(null, null);
        $this->assertCount(1, $result);
        $this->assertTrue($this->user->deregister());
        $result = UserPost::findAllByIdentityInBatch();
        $this->assertCount(0, $result);
    }

    /**
     * @group blameable
     * @group post
     */
    public function testFindOneById()
    {
        $model = null;
        try {
            $model = UserPost::findOneById($this->post->getID(), true, $this->user);
            $this->fail();
        } catch (\Exception $ex) {
            $this->assertNotNull($ex);
            $this->assertEquals('Model Not Found.', $ex->getMessage());
        }
        
        try {
            $model = UserPost::findOneById($this->post->getID(), false, $this->user);
            $this->assertNull($model);
        } catch (\Exception $ex) {
            $this->fail();
        }
        
        $this->assertTrue($this->user->register(array_merge([$this->post], $this->comments)));
        
        try {
            $model = UserPost::findOneById($this->post->getID(), true, $this->user);
            $this->assertInstanceOf(UserPost::class, $model);
        } catch (\Exception $ex) {
            $this->fail();
        }
        
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group blameable
     * @group post
     */
    public function testFindByIdentity()
    {
        $result = UserPost::findByIdentity($this->user)->all();
        $this->assertCount(0, $result);
        $this->assertTrue($this->user->register(array_merge([$this->post], $this->comments)));
        $result = UserPost::findByIdentity($this->user)->all();
        $this->assertCount(1, $result);
        $this->assertTrue($this->user->deregister());
        $result = UserPost::findByIdentity($this->user)->all();
        $this->assertCount(0, $result);
    }

    /**
     * @group blameable
     * @group post
     */
    public function testCountByIdentity()
    {
        $this->assertTrue($this->user->register(array_merge([$this->post], $this->comments)));
        $this->assertEquals(1, UserPost::countByIdentity($this->user));
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group blameable
     * @group post
     */
    public function testFindByCreator()
    {
        $results = UserPost::find()->createdBy($this->user)->all();
        $this->assertCount(0, $results);
        $this->assertTrue($this->user->register(array_merge([$this->post], $this->comments)));
        $results = UserPost::find()->createdBy($this->user)->all();
        $this->assertCount(1, $results);
        $this->assertTrue($this->user->deregister());
        $results = UserPost::find()->createdBy($this->user)->all();
        $this->assertCount(0, $results);
    }

    /**
     * @group blameable
     * @group post
     */
    public function testFindByUpdater()
    {
        $results = UserPost::find()->updatedBy($this->user)->all();
        $this->assertCount(0, $results);
        $this->assertTrue($this->user->register(array_merge([$this->post], $this->comments)));
        $results = UserPost::find()->updatedBy($this->user)->all();
        $this->assertCount(1, $results);
        $this->assertTrue($this->user->deregister());
        $results = UserPost::find()->updatedBy($this->user)->all();
        $this->assertCount(0, $results);
    }

    /**
     * @group blameable
     * @group post
     */
    public function testFindByContent()
    {
        $results = UserPost::find()->updatedBy($this->user)->content($this->post->getContent())->all();
        $this->assertCount(0, $results);
        $this->assertTrue($this->user->register(array_merge([$this->post], $this->comments)));
        $results = UserPost::find()->updatedBy($this->user)->content($this->post->getContent())->all();
        $this->assertCount(1, $results);
        $results = UserPost::find()->updatedBy($this->user)->content(substr($this->post->getContent(), 0, 1))->all();
        $this->assertCount(0, $results);
        $results = UserPost::find()->updatedBy($this->user)->content(substr($this->post->getContent(), 0, 1), 'like')->all();
        $this->assertCount(1, $results);
        $results = UserPost::find()->updatedBy($this->user)->content($this->post->getContent() . '1')->all();
        $this->assertCount(0, $results);
        $this->assertTrue($this->user->deregister());
        $results = UserPost::find()->updatedBy($this->user)->content($this->post->getContent())->all();
        $this->assertCount(0, $results);
    }

    /**
     * @group blameable
     * @group post
     */
    public function testFalseParentAttribute()
    {
        try {
            $this->post->bear();
            $this->fail();
        } catch (\Exception $ex) {
            $this->assertEquals("Parent Attribute Not Determined.", $ex->getMessage());
        }
        
        $this->assertEmpty($this->post->getAncestorChain());
        $this->assertEmpty($this->post->getCommonAncestor($this));
    }

    /**
     * @group blameable
     * @group post
     */
    public function testFindByGuid()
    {
        $this->assertTrue($this->user->register([$this->post]));
        $post = UserPost::find()->guid($this->post->getGUID())->parentGuid($this->post->getGUID())->one();
        $this->assertInstanceOf(UserPost::class, $post);
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group blameable
     * @group post
     */
    public function testSetHost()
    {
        $this->assertTrue($this->user->register([$this->post]));
        $this->assertTrue($this->other->register());
        $post = UserPost::find()->createdBy($this->user)->one();
        $this->assertInstanceOf(UserPost::class, $post);
        $this->assertNull(UserPost::find()->createdBy($this->other)->one());
        
        $this->post->setHost($this->other->getReadableGUID());
        $this->assertTrue($this->post->save());
        $this->assertInstanceOf(UserPost::class, UserPost::find()->createdBy($this->other)->one());
        $this->assertNull(UserPost::find()->createdBy($this->user)->one());
        
        $this->assertFalse($this->post->setHost(null));
        $this->assertTrue($this->user->deregister());
    }
}
