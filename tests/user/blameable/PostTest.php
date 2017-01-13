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

/**
 * @author vistart <i@vistart.me>
 */
class PostTest extends BlameableTestCase
{
    /**
     * @group blameable
     */
    public function testNew()
    {
        $this->assertTrue($this->user->register(array_merge([$this->post], $this->comments)));
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group blameable
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
     */
    public function testUpdater()
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
     */
    public function testHasEverEdited()
    {
        $this->assertTrue($this->user->register(array_merge([$this->post], $this->comments)));
        $this->assertFalse($this->post->hasEverEdited());
        foreach ($this->comments as $c) {
            /* @var $c UserComment */
            $this->assertFalse($c->hasEverEdited());
        }
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group blameable
     */
    public function testEnabledFields()
    {
        $this->assertTrue($this->user->register(array_merge([$this->post], $this->comments)));
        $this->assertNotEmpty($this->post->enabledFields());
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group blameable
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
        $this->assertTrue($this->user->deregister());
        $result = UserPost::findAllByIdentityInBatch();
        $this->assertCount(0, $result);
    }
    
    /**
     * @group blameable
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
     */
    public function testCountByIdentity()
    {
        $this->assertTrue($this->user->register(array_merge([$this->post], $this->comments)));
        $this->assertEquals(1, UserPost::countByIdentity($this->user));
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group blameable
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
}