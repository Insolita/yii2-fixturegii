<?php
/**
 * Created by solly [25.05.17 1:59]
 */

namespace tests\unit;

use Codeception\Specify;
use Codeception\Test\Unit;
use insolita\fixturegii\services\FakerColumnResolver;

class FakerColumnResolverTest extends Unit
{
    use Specify;
    
    public function testBuildFakerColumnsData()
    {
        $this->specify('testMysql',function(){
             $db = \Yii::$app->db;
             $columns = $db->getTableSchema('migrik_test3')->columns;
             $resolver = new FakerColumnResolver($columns);
             $result = $resolver->buildFakerColumnsData();
             verify(count($result))->equals(15);
             $colList = $db->getTableSchema('migrik_test3')->getColumnNames();
             foreach ($colList as $name){
                 verify($name, $result)->hasKey($name);
             }
             verify($result['boolField'])->contains('$faker->optional()->boolean');
             verify($result['doubleField'])->contains('$faker->randomFloat');
             verify($result['decimalField'])->contains('$faker->randomFloat');
        });
        $this->specify('testMysqlSpec',function(){
            $db = \Yii::$app->db;
            $columns = $db->getTableSchema('migrik_myspec')->columns;
            $resolver = new FakerColumnResolver($columns);
            $result = $resolver->buildFakerColumnsData();
            $colList = $db->getTableSchema('migrik_myspec')->getColumnNames();
            foreach ($colList as $name){
                verify($name, $result)->hasKey($name);
            }
            verify($result['enum'])->contains('$faker->randomElement');
            verify($result['set'])->contains('$faker->randomElement');
            verify($result['timeStampField'])->contains('$faker->dateTimeThisMonth');
        });
        $this->specify('testPgSql',function(){
            $db = \Yii::$app->pgdb;
            $columns = $db->getTableSchema('migrik_test3')->columns;
            $resolver = new FakerColumnResolver($columns);
            $result = $resolver->buildFakerColumnsData();
            verify(count($result))->equals(15);
            $colList = $db->getTableSchema('migrik_test3')->getColumnNames();
            foreach ($colList as $name){
                verify($name, $result)->hasKey($name);
            }
            verify($result['boolField'])->contains('$faker->optional()->boolean');
            verify($result['doubleField'])->contains('$faker->randomFloat');
            verify($result['decimalField'])->contains('$faker->randomFloat');
        });
        $this->specify('testPgSpec',function(){
            $db = \Yii::$app->pgdb;
            $columns = $db->getTableSchema('migrik_pgspec')->columns;
            $resolver = new FakerColumnResolver($columns);
            $result = $resolver->buildFakerColumnsData();
            $colList = $db->getTableSchema('migrik_pgspec')->getColumnNames();
            foreach ($colList as $name){
                verify($name, $result)->hasKey($name);
            }
            verify($result['arrField'])->contains('$faker->randomElement');
            verify($result['arrField2'])->contains('$faker->randomElement');
            verify($result['jsonField'])->contains('{}');
            verify($result['binaryField'])->contains('null');
        });
    }
}
