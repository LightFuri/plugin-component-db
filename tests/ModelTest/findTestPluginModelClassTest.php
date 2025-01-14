<?php


namespace Leadvertex\Plugin\Components\Db\ModelTest;


use Leadvertex\Plugin\Components\Db\Components\Connector;
use Leadvertex\Plugin\Components\Db\Components\PluginReference;
use Leadvertex\Plugin\Components\Db\Components\TestPluginModelClass;
use Medoo\Medoo;
use PHPUnit\Framework\TestCase;
use function Symfony\Component\String\b;

class findTestPluginModelClassTest extends TestCase
{

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        Connector::config(new Medoo([
            'database_type' => 'sqlite',
            'database_file' => __DIR__ . '/../../testDB.db'
        ]));

        Connector::setReference(new PluginReference(1, 'user', 2));
    }

    public function testFindByCondition()
    {
        $results = TestPluginModelClass::findByCondition(['value_1' => '10']);
        foreach ($results as $key => $result)
        {
            $this->assertArrayHasKey($key, $results);
            $this->assertInstanceOf('Leadvertex\Plugin\Components\Db\Components\TestPluginModelClass', $result);
            $this->assertEquals($key, $result->getId());
            $this->assertEquals(10, $result->value_1);
            if ($key === 3) {
                $this->assertEquals('Hello world 2', $result->value_2);
            } else {
                $this->assertEquals('Hello world', $result->value_2);
            }
        }
    }

    public function testFindByConditionValue2()
    {
        $results = TestPluginModelClass::findByCondition(['value_2' => 'Hello world']);
        foreach ($results as $key => $result) {
            $this->assertArrayHasKey($key, $results);
            $this->assertInstanceOf('Leadvertex\Plugin\Components\Db\Components\TestPluginModelClass', $result);
            $this->assertEquals($key, $result->getId());
            $this->assertEquals(10, $result->value_1);
            $this->assertEquals('Hello world', $result->value_2);
        }
    }

    public function testFindByConditionNotFount()
    {
        $this->assertEmpty(TestPluginModelClass::findByCondition(['value_1' => '111']));
    }

    public function testFindByConditionTwoFilter()
    {
        $this->assertEmpty(TestPluginModelClass::findByCondition(['value_1' => '11', 'value_2' => 'Hello world']));
    }

    public function testFindByConditionEmpty()
    {
        $results = TestPluginModelClass::findByCondition([]);
        foreach ($results as $key => $result) {
            $this->assertArrayHasKey($key, $results);
            $this->assertInstanceOf('Leadvertex\Plugin\Components\Db\Components\TestPluginModelClass', $result);
            $this->assertEquals($key, $result->getId());
            switch ($key) {
                case 1:
                    $expected_1 = 11;
                    $expected_2 = 'Hello world 1';
                    break;
                case 2:
                    $expected_1 = 10;
                    $expected_2 = 'Hello world';
                    break;
                case 3:
                    $expected_1 = 10;
                    $expected_2 = 'Hello world 2';
                    break;
                default:
                    $expected_1 = '';
                    $expected_2 = '';
            }
            $this->assertEquals($expected_1, $result->value_1);
            $this->assertEquals($expected_2, $result->value_2);
        }
    }

    public function testFindByConditionNull()
    {
        $this->assertEmpty(TestPluginModelClass::findByCondition([null]));
    }

    public function testFindByIds()
    {
        $results = TestPluginModelClass::findByIds([2, 1]);
        foreach ($results as $key => $result) {
            $this->assertArrayHasKey($key, $results);
            $this->assertInstanceOf('Leadvertex\Plugin\Components\Db\Components\TestPluginModelClass', $result);
            $this->assertEquals($key, $result->getId());
            switch ($key) {
                case 1:
                    $expected_1 = 11;
                    $expected_2 = 'Hello world 1';
                    break;
                case 2:
                    $expected_1 = 10;
                    $expected_2 = 'Hello world';
                    break;
                default:
                    $expected_1 = '';
                    $expected_2 = '';
            }
            $this->assertEquals($expected_1, $result->value_1);
            $this->assertEquals($expected_2, $result->value_2);
        }
    }

    public function testFindByIdsWithNotExistId()
    {
        $results = TestPluginModelClass::findByIds([2, 1, 11]);
        foreach ($results as $key => $result) {
            $this->assertArrayHasKey($key, $results);
            $this->assertInstanceOf('Leadvertex\Plugin\Components\Db\Components\TestPluginModelClass', $result);
            $this->assertEquals($key, $result->getId());
            switch ($key) {
                case 1:
                    $expected_1 = 11;
                    $expected_2 = 'Hello world 1';
                    break;
                case 2:
                    $expected_1 = 10;
                    $expected_2 = 'Hello world';
                    break;
                default:
                    $expected_1 = '';
                    $expected_2 = '';
            }
            $this->assertEquals($expected_1, $result->value_1);
            $this->assertEquals($expected_2, $result->value_2);
        }
    }

    public function testFindByIdsNotFound()
    {
        $this->assertEmpty(TestPluginModelClass::findByIds([11]));
    }

    public function testFindByIdsEmpty()
    {
        $this->assertEmpty(TestPluginModelClass::findByIds([]));
    }

    public function testFindByIdsNull()
    {
        $this->assertEmpty(TestPluginModelClass::findByIds([null]));
    }

    public function testFindById()
    {
        $result = TestPluginModelClass::findById( 1);
        $this->assertInstanceOf('Leadvertex\Plugin\Components\Db\Components\TestPluginModelClass', $result);
        $this->assertEquals(1, $result->getId());
        $this->assertEquals(11, $result->value_1);
        $this->assertEquals('Hello world 1', $result->value_2);
    }

    public function testFindByIdNotFound()
    {
        $this->assertNull(TestPluginModelClass::findById( 11));
    }

    public function testFindByConditionWithoutScope()
    {
        $results = TestPluginModelClass::findByCondition(['value_1' => 10], false);
        $this->assertArrayHasKey('0', $results);
        $this->assertArrayHasKey('1', $results);
        $this->assertArrayHasKey('2', $results);
        $this->assertCount(3, $results);
    }

    public function testFindByConditionEmptyWithoutScope()
    {
        $results = TestPluginModelClass::findByCondition([], false);
        $this->assertArrayHasKey('0', $results);
        $this->assertArrayHasKey('1', $results);
        $this->assertArrayHasKey('2', $results);
        $this->assertArrayHasKey('3', $results);
        $this->assertCount(4, $results);
    }

}