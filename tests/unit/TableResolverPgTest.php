<?php
/**
 * Created by solly [11.08.16 5:34]
 */

namespace tests\unit;

use Codeception\Specify;
use Codeception\Test\Unit;
use Codeception\Util\Debug;
use Codeception\Verify;
use insolita\fixturegii\objects\TableIndex;
use insolita\fixturegii\objects\TableRelation;
use insolita\fixturegii\services\TableResolver;
use yii\db\Schema;
use yii\db\TableSchema;
use yii\helpers\ArrayHelper;

/**
 * @var Verify
 **/
class TableResolverPgTest extends Unit
{
    use Specify;
    
    /**@var \yii\db\Connection* */
    protected $db;
    
    public function testGetSchema()
    {
        $resolver = new TableResolver($this->db);
        $schema = $resolver->schema;
        verify($schema)->isInstanceOf(Schema::class);
        verify($schema)->isInstanceOf(\yii\db\pgsql\Schema::class);
        
    }
    
    public function testGetTableCaption()
    {
        $resolver = new TableResolver($this->db);
        $caption = $resolver->getTableCaption('migrik_test1');
        \verify($caption)->equals('migrik_test1');
        $this->db->tablePrefix = 'migrik';
        $resolver = new TableResolver($this->db);
        $caption = $resolver->getTableCaption('migrik_test1');
        \verify($caption)->equals('test1');
    }
    
    public function testGetTableAlias()
    {
        $resolver = new TableResolver($this->db);
        $alias = $resolver->getTableAlias('migrik_test1');
        \verify($alias)->equals('{{%migrik_test1}}');
        $this->db->tablePrefix = 'migrik';
        $resolver = new TableResolver($this->db);
        $alias = $resolver->getTableAlias('migrik_test1');
        \verify($alias)->equals('{{%test1}}');
    }
    
    public function testExtractTablesFromMaskedList()
    {
        $fixture = [
            ['tables' => 'migrik_test1', 'ignors' => '', 'expect' => ['migrik_test1']],
            [
                'tables' => 'migrik_test*',
                'ignors' => '',
                'expect' => ['migrik_test1', 'migrik_test2', 'migrik_test3', 'migrik_testfk', 'migrik_testcomposite'],
            ],
            [
                'tables' => '*test*',
                'ignors' => '',
                'expect' => ['migrik_test1', 'migrik_test2', 'migrik_test3', 'migrik_testfk', 'migrik_testcomposite'],
            ],
            ['tables' => '*test*', 'ignors' => '*1,*2,*3', 'expect' => ['migrik_testfk', 'migrik_testcomposite']],
        ];
        $resolver = new TableResolver($this->db);
        foreach ($fixture as $row) {
            $result = $resolver->extractTablesFromMaskedList($row['tables'], $row['ignors']);
            \verify(count($result))->greaterOrEquals(1);
            foreach ($row['expect'] as $check) {
                \verify($check, '|(' . implode(',', $result) . ')', $result)->contains($check);
            }
        }
        $result = $resolver->extractTablesFromMaskedList('qwertyu','');
        \verify($result)->isEmpty();
    }
    
    public function testGetTableSchema()
    {
        $resolver = new TableResolver($this->db);
        
        $tableSchema = $resolver->getTableSchema('migrik_test1');
        verify_that($tableSchema);
        verify($tableSchema)->isInstanceOf(TableSchema::class);
    }
    
    public function testGetTableNames()
    {
        $resolver = new TableResolver($this->db);
        
        $tableNames = $resolver->getTableNames();
        verify($tableNames)->notEmpty();
        verify($tableNames)->contains('migrik_test1');
        verify($tableNames)->contains('migrik_test2');
    }
    
    public function testFindTablesByPattern()
    {
        $resolver = new TableResolver($this->db);
        
        $this->specify(
            'by table name',
            function () use ($resolver) {
                $founds = $resolver->findTablesByPattern('migrik_testcomposite');
                verify($founds)->notEmpty();
                verify($founds)->contains('migrik_testcomposite');
                verify(count($founds))->equals(1);
            }
        );
        
        $this->specify(
            'by pattern one result',
            function () use ($resolver) {
                $founds = $resolver->findTablesByPattern('migrik_testcompos*');
                verify($founds)->notEmpty();
                verify($founds)->contains('migrik_testcomposite');
                verify(count($founds))->equals(1);
            }
        );
        
        $this->specify(
            'by pattern bulk result',
            function () use ($resolver) {
                $founds = $resolver->findTablesByPattern('migrik_test*');
                verify($founds)->notEmpty();
                verify($founds)->contains('migrik_testcomposite');
                verify($founds)->contains('migrik_testfk');
                verify(count($founds))->greaterOrEquals(5);
            }
        );
    }
    
    public function testGetRelations()
    {
        $resolver = new TableResolver($this->db);
        $this->specify(
            'by no relationed table',
            function () use ($resolver) {
                $founds = $resolver->getRelations('migrik_testunexisted');
                verify($founds)->isEmpty();
            }
        );
        
        $this->specify(
            'by  relationed table',
            function () use ($resolver) {
                $founds = $resolver->getRelations('migrik_testfk');
                verify($founds)->notEmpty();
                verify(count($founds))->equals(1);
                $relation = reset($founds);
                verify($relation)->isInstanceOf(TableRelation::class);
                \verify($relation->getTableName())->equals('migrik_test3');
                \verify($relation->isComposite())->false();
                \verify($relation->getFk())->equals(['extId']);
                \verify($relation->getRelatedIds())->equals(['ids']);
                \verify($relation->getName())->equals('someIdx');
                Debug::debug($founds);
            }
        );
    }
    
    public function testGetIndexes()
    {
        $resolver = new TableResolver($this->db);
        
        $this->specify(
            'not indexed',
            function () use ($resolver) {
                $founds = $resolver->getIndexes('migrik_test3');
                verify(count($founds))->equals(0);
            }
        );
        
        $this->specify(
            'indexed',
            function () use ($resolver) {
                $founds = $resolver->getIndexes('migrik_test2');
                verify(count($founds))->equals(1);
                $index = reset($founds);
                \verify($index)->isInstanceOf(TableIndex::class);
                \verify($index->getName())->equals('strFieldUniq');
                \verify($index->isMultiColumn())->equals(false);
                \verify($index->isUnique())->equals(true);
                \verify($index->getColumns())->equals(['strField']);
                $founds = $resolver->getIndexes('migrik_test1');
                verify(count($founds))->equals(2);
                $founds = ArrayHelper::index($founds, 'name');
                \verify($founds)->hasKey('complexIdx');
                \verify($founds)->hasKey('migrik_test1_smallintField_key');
            }
        );
        $this->specify(
            'pgspec',
            function () use ($resolver) {
                $founds = $resolver->getIndexes('migrik_pgspec');
                verify(count($founds))->equals(1);
            }
        );
        $this->specify(
            'composite',
            function () use ($resolver) {
                $founds = $resolver->getIndexes('migrik_testcomposite');
                verify(count($founds))->equals(0);
            }
        );
    }
    
    public function testGetPimaryKeys()
    {
        $resolver = new TableResolver($this->db);
        $this->specify(
            'simplePk',
            function () use ($resolver) {
                $pk = $resolver->getPrimaryKeys('migrik_test1');
                verify(is_array($pk))->true();
                verify(count($pk))->equals(1);
                verify($pk[0])->equals('id');
                
                $pk = $resolver->getPrimaryKeys('migrik_test2');
                verify(is_array($pk))->true();
                verify(count($pk))->equals(1);
                verify($pk[0])->equals('id');
            }
        );
        $this->specify(
            'noPk',
            function () use ($resolver) {
                $pk = $resolver->getPrimaryKeys('migrik_pgspec');
                verify(is_array($pk))->true();
                verify(count($pk))->equals(0);
            }
        );
        $this->specify(
            'compositePk',
            function () use ($resolver) {
                $pk = $resolver->getPrimaryKeys('migrik_testcomposite');
                verify(is_array($pk))->true();
                verify(count($pk))->equals(2);
                Debug::debug($pk);
            }
        );
    }
    
    protected function _before()
    {
        $this->db = \Yii::$app->get('pgdb');
        Debug::debug($this->db->dsn);
    }
    
}