<?php


namespace Leadvertex\Plugin\Components\Db\ModelTest;


use Leadvertex\Plugin\Components\Db\Components\Connector;
use Leadvertex\Plugin\Components\Db\Components\PluginReference;
use Leadvertex\Plugin\Components\Db\Components\TestSinglePluginModelClass;
use Medoo\Medoo;
use PHPUnit\Framework\TestCase;

class findTestSinglePluginModelClassTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
        Connector::config(new Medoo([
            'database_type' => 'sqlite',
            'database_file' => __DIR__ . '/../../testDB.db'
        ]));

        Connector::setReference(new PluginReference(1, 'user', 2));
    }

    public function testFindByCondition()
    {
        $this->assertModels(TestSinglePluginModelClass::findByCondition(['value_1' => '10']));
    }

    public function testFindByConditionValue2()
    {
        $this->assertModels(TestSinglePluginModelClass::findByCondition(['value_2' => 'Hello world']));
    }

    public function testFindByConditionNotFount()
    {
        $this->assertEmpty(TestSinglePluginModelClass::findByCondition(['value_1' => '111']));
    }

    public function testFindByConditionTwoFilter()
    {
        $this->assertEmpty(TestSinglePluginModelClass::findByCondition(['value_1' => '11', 'value_2' => 'Hello world']));
    }

    public function testFindByConditionEmpty()
    {
        $this->assertModels(TestSinglePluginModelClass::findByCondition([]));
    }

    public function testFindByConditionNull()
    {
        $this->assertEmpty(TestSinglePluginModelClass::findByCondition([null]));
    }

    public function testFindByIds()
    {
        $this->assertModels(TestSinglePluginModelClass::findByIds([2, 1]));
    }

    public function testFindByIdsWithNotExistId()
    {
        $this->assertModels(TestSinglePluginModelClass::findByIds([2, 1, 11]));
    }

    public function testFindByIdsNotFound()
    {
        $this->assertEmpty(TestSinglePluginModelClass::findByIds([11]));
    }

    public function testFindByIdsEmpty()
    {
        $this->assertEmpty(TestSinglePluginModelClass::findByIds([]));
    }

    public function testFindByIdsNull()
    {
        $this->assertEmpty(TestSinglePluginModelClass::findByIds([null]));
    }

    public function testFindById()
    {
        $this->assertModel(TestSinglePluginModelClass::findById( 2));
    }

    public function testFindByIdNotFound()
    {
        $this->assertNull(TestSinglePluginModelClass::findById( 11));
    }

    public function testFind()
    {
        $this->assertModel(TestSinglePluginModelClass::find());
    }

    public function assertModel($result)
    {
        $this->assertInstanceOf('Leadvertex\Plugin\Components\Db\Components\TestSinglePluginModelClass', $result);
        $this->assertEquals(2, $result->getId());
        $this->assertEquals(10, $result->value_1);
        $this->assertEquals('Hello world', $result->value_2);
    }

    public function assertModels($results)
    {
        $this->assertCount(1, $results);
        foreach ($results as $result) {
            $this->assertArrayHasKey(2, $results);
            $this->assertInstanceOf('Leadvertex\Plugin\Components\Db\Components\TestSinglePluginModelClass', $result);
            $this->assertEquals(2, $result->getId());
            $this->assertEquals(10, $result->value_1);
            $this->assertEquals('Hello world', $result->value_2);
        }
    }

    public function testFindByConditionWithoutScope()
    {
        $results = TestSinglePluginModelClass::findByCondition(['value_2' => 'Hello world'], false);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('0', $results);
        $this->assertArrayHasKey('1', $results);
    }

    public function testFindByConditionEmptyWithoutScope()
    {
        $results = TestSinglePluginModelClass::findByCondition([], false);
        $this->assertCount(4, $results);
        $this->assertArrayHasKey('0', $results);
        $this->assertArrayHasKey('1', $results);
        $this->assertArrayHasKey('2', $results);
        $this->assertArrayHasKey('3', $results);
    }
}