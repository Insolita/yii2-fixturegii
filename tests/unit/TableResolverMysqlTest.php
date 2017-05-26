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
class TableResolverMysqlTest extends Unit
{
    use Specify;
    
    protected $db;
    protected function _before()
    {
        $this->db =  \Yii::$app->dbmm;
        Debug::debug($this->db->dsn);
    }

    public function testGetSchema()
    {
        $resolver = new TableResolver($this->db);
        $schema = $resolver->schema;
        verify($schema)->isInstanceOf(Schema::class);
        verify($schema)->isInstanceOf(\yii\db\mysql\Schema::class);
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
                verify(count($founds))->equals(2);
                $founds = ArrayHelper::index($founds, 'name');
                $index = $founds['strFieldUniq'];
                \verify($index)->isInstanceOf(TableIndex::class);
                \verify($index->getName())->equals('strFieldUniq');
                \verify($index->isMultiColumn())->equals(false);
                \verify($index->isUnique())->equals(true);
                \verify($index->getColumns())->equals(['strField']);
    
                $founds = $resolver->getIndexes('migrik_test1');
                verify(count($founds))->equals(3);
                $founds = ArrayHelper::index($founds, 'name');
                \verify($founds)->hasKey('PRIMARY');
                \verify($founds)->hasKey('complexIdx');
                \verify($founds)->hasKey('migrik_test1_smallintField_key');
    
            }
        );
            $this->specify('myspec',function () use($resolver){
                $founds = $resolver->getIndexes('migrik_myspec');
                verify(count($founds))->equals(1);
                $founds = ArrayHelper::index($founds, 'name');
                \verify($founds)->hasKey('id');
            });
    }
    
    public function testGetPimaryKeys()
    {
        $resolver = new TableResolver($this->db);
        $this->specify(
            'simplePk',
            function () use ($resolver){
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
            'compositePk',
            function () use ($resolver){
                $pk = $resolver->getPrimaryKeys('migrik_testcomposite');
                verify(is_array($pk))->true();
                verify(count($pk))->equals(2);
                Debug::debug($pk);
            }
        );
    }
    
}