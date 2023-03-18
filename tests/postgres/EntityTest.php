<?php

namespace postgres;

use rhosocial\base\models\db\pgsql\BinaryExpression;
use rhosocial\base\models\db\pgsql\BinaryExpressionBuilder;
use rhosocial\base\models\tests\data\ar\Entity;
use rhosocial\base\models\tests\entity\EntityTestCase;

class EntityTest extends EntityTestCase
{
    /**
     * @group postgres
     * @return void
     */
    public function testNew(): void
    {
        $db = Entity::getDb();
        $db->setQueryBuilder(["expressionBuilders"=> [
            BinaryExpression::class => BinaryExpressionBuilder::class
        ]]);
        $this->assertTrue($this->entity->save());
        print("success");
    }
}