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

namespace rhosocial\base\models\tests\entity;

use rhosocial\base\models\tests\data\ar\Entity;
use yii\base\Exception;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class EntityTest extends EntityTestCase
{
    /**
     * @group entity
     */
    public function testAttributes()
    {
        $this->assertInstanceOf(Entity::class, $this->entity);
        $this->assertTrue($this->entity->checkAttributes());
    }

    /**
     * @group entity
     */
    public function testInvalidAttributes()
    {
        try {
            $this->entity = new Entity(['idAttribute' => false, 'guidAttribute' => false]);
            $this->fail();
        } catch (\Exception $ex) {
            $this->assertTrue(true);
        }
    }

    /**
     * @group entity
     */
    public function testSelfFields()
    {
        $unsetArray = $this->entity->unsetSelfFields();
        if (empty($unsetArray)) {
            $this->fail();
        } else {
            $this->assertArrayHasKey('content', $unsetArray);
            $this->assertArrayHasKey('expired_after', $unsetArray);
        }
    }

    /**
     * @group entity
     * @group timestamp
     * @throws Exception|\Throwable
     */
    public function testHasEverBeenEdited()
    {
        $this->assertNotEmpty($this->entity->createdAtAttribute);
        $this->assertNotEmpty($this->entity->updatedAtAttribute);
        $this->assertNotEquals($this->entity->createdAtAttribute, $this->entity->updatedAtAttribute);
        $this->assertTrue($this->entity->save());
        $this->assertFalse($this->entity->hasEverBeenEdited());
        sleep(1);
        $this->assertTrue($this->entity->save());
        $this->assertEquals($this->entity->getCreatedAt(), $this->entity->getUpdatedAt());
        $this->assertFalse($this->entity->hasEverBeenEdited());
        
        $this->entity->content = \Yii::$app->security->generateRandomString();
        sleep(1);
        $this->assertTrue($this->entity->save());
        $this->assertNotEquals($this->entity->getCreatedAt(), $this->entity->getUpdatedAt());
        $this->assertTrue($this->entity->hasEverBeenEdited());
        
        $this->assertGreaterThanOrEqual(1, $this->entity->delete());
    }

    /**
     * @group entity
     * @group timestamp
     * @throws Exception
     */
    public function testOrderByCreatedAtASC()
    {
        $entities = [];
        for ($i = 0; $i < 10; $i++)
        {
            $entity = new Entity(['content' => \Yii::$app->security->generateRandomString()]);
            sleep(1);
            $this->assertTrue($entity->save());
            $entities[] = $entity;
        }
        $models = Entity::find()->orderByCreatedAt()->all();
        $this->assertCount(10, $models);
        
        for ($i = 0; $i < 10; $i++)
        {
            $this->assertTrue($models[$i]->equals($entities[$i]));
        }
    }

    /**
     * @group entity
     * @group timestamp
     * @throws Exception
     */
    public function testOrderByCreatedAtDESC()
    {
        $entities = [];
        for ($i = 0; $i < 10; $i++)
        {
            $entity = new Entity(['content' => \Yii::$app->security->generateRandomString()]);
            sleep(1);
            $this->assertTrue($entity->save());
            $entities[] = $entity;
        }
        $models = Entity::find()->orderByCreatedAt(SORT_DESC)->all();
        $this->assertCount(10, $models);
    
        for ($i = 0; $i < 10; $i++)
        {
            $this->assertTrue($models[9 - $i]->equals($entities[$i]), "models[9-$i]:" . $models[9-$i]->getGUID() . '|' . "entities[$i]:" . $entities[$i]->getGUID());
        }
    }

    /**
     * @group entity
     * @group timestamp
     * @throws Exception
     */
    public function testOrderByUpdatedAtASC()
    {
        $entities = [];
        for ($i = 0; $i < 10; $i++)
        {
            $entity = new Entity(['content' => \Yii::$app->security->generateRandomString()]);
            sleep(1);
            $this->assertTrue($entity->save());
            $entities[] = $entity;
        }
        $models = Entity::find()->orderByUpdatedAt()->all();
        $this->assertCount(10, $models);
        
        for ($i = 0; $i < 10; $i++)
        {
            $this->assertTrue($models[$i]->equals($entities[$i]));
        }
    }

    /**
     * @group entity
     * @group timestamp
     * @throws Exception
     */
    public function testOrderByUpdatedAtDESC()
    {
        $entities = [];
        for ($i = 0; $i < 10; $i++)
        {
            $entity = new Entity(['content' => \Yii::$app->security->generateRandomString()]);
            sleep(1);
            $this->assertTrue($entity->save());
            $entities[] = $entity;
        }
        $models = Entity::find()->orderByUpdatedAt(SORT_DESC)->all();
        $this->assertCount(10, $models);
        
        for ($i = 0; $i < 10; $i++)
        {
            $this->assertTrue($models[9 - $i]->equals($entities[$i]));
        }
    }

    /**
     * @group entity
     * @group query
     * @throws Exception
     */
    public function testGuidOrId()
    {
        $entities = [];
        for ($i = 0; $i < 10; $i++) {
            $entity = new Entity(['content' => \Yii::$app->security->generateRandomString()]);
            $this->assertTrue($entity->save());
            $entities[] = $entity;
        }
        $model = Entity::find()->guidOrId($entities[0]->getGUID())->one();

        $this->assertEquals($model->getID(), $entities[0]->getID());
        $this->assertEquals($model->getGUID(), $entities[0]->getGUID());

        $this->assertNotEquals($model->getID(), $entities[1]->getID());
        $this->assertNotEquals($model->getGUID(), $entities[2]->getGUID());

        $model = Entity::find()->guidOrId($entities[7]->getID())->one();

        $this->assertEquals($model->getGUID(), $entities[7]->getGUID());
        $this->assertEquals($model->getID(), $entities[7]->getID());

        $this->assertNotEquals($model->getGUID(), $entities[8]->getGUID());
        $this->assertNotEquals($model->getID(), $entities[8]->getID());
    }

    /**
     * @group entity
     * @group timestamp
     */
    public function testCreatedAtToday()
    {
        $this->assertEquals(0, (int)Entity::find()->createdAtToday()->count());
        $this->assertTrue($this->entity->save());
        $entity = Entity::find()->createdAtToday()->one();
        $this->assertEquals($this->entity->guid, $entity->guid);
        $this->assertEquals(1, $this->entity->delete());
    }

    /**
     * @group entity
     * @group timestamp
     */
    public function testUpdatedAtToday()
    {
        $this->assertEquals(0, (int)Entity::find()->updatedAtToday()->count());
        $this->assertTrue($this->entity->save());
        $entity = Entity::find()->updatedAtToday()->one();
        $this->assertEquals($this->entity->guid, $entity->guid);
        $this->assertEquals(1, $this->entity->delete());
    }
}
