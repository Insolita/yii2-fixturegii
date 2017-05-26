<?php
/**
 * Created by solly [14.08.16 23:02]
 */

namespace insolita\fixturegii\services;

use insolita\fixturegii\objects\TableIndex;
use insolita\fixturegii\objects\TableRelation;
use yii\db\Connection;
use yii\db\Query;
use yii\db\TableSchema;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

/**
 * Class TableResolver
 *
 * @package insolita\fixturegii\services
 */
class TableResolver
{
    /**
     * @var \yii\db\Schema
     */
    public $schema;
    
    /**
     * @var array
     */
    protected $tableSchemas = [];
    
    /**
     * @var \yii\db\Connection
     */
    private $connection;
    
    /**
     * Base constructor.
     *
     * @param Connection $connection
     */
    public function __construct(\yii\db\Connection $connection)
    {
        $this->connection = $connection;
        $this->schema = $this->connection->getSchema();
    }
    
    /**
     * @param string $tableList
     * @param string $ignoreList
     */
    public function extractTablesFromMaskedList($tableList, $ignoreList)
    {
        $ignoreList = $this->extractTablesFromList($ignoreList);
        $tableList = $this->extractTablesFromList($tableList);
        return array_filter(
            $tableList,
            function ($v) use ($ignoreList) {
                return !in_array($v, $ignoreList);
            }
        );
        
    }
    
    /**
     * @param $tableName
     *
     * @return mixed
     */
    public function getTableCaption($tableName)
    {
        return str_replace($this->connection->tablePrefix, '', strtolower($tableName));
    }
    
    /**
     * @param $tableName
     *
     * @return string
     */
    public function getTableAlias($tableName)
    {
        return '{{%' . $this->getTableCaption($tableName) . '}}';
    }
    
    /**
     * @param string $tablePattern
     *
     * @return array
     **/
    public function findTablesByPattern($tablePattern)
    {
        $founds = [];
        $tablePattern = trim($tablePattern);
        if ($tablePattern == '*') {
            foreach ($this->getTableNames() as $table) {
                $founds[] = $table;
            }
        } elseif (strpos($tablePattern, '*') !== false) {
            $pattern = '/^' . str_replace('*', '\w+', $tablePattern) . '$/';
            foreach ($this->getTableNames() as $table) {
                if (preg_match($pattern, $table)) {
                    $founds[] = $table;
                }
            }
            
        } elseif ($this->getTableSchema($tablePattern) !== null) {
            $founds[] = $tablePattern;
        }
        return $founds;
    }
    
    /**
     * @param $tableName
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getTableData($tableName, $limit, $offset)
    {
        return (new Query())->select('*')
                            ->from($tableName)->limit($limit)->offset($offset)->all($this->connection);
    }
    
    /**
     * @return array
     **/
    public function getTableNames()
    {
        return $this->schema->tableNames;
    }
    
    /**
     * @param $tableName
     *
     * @return TableSchema
     */
    public function getTableSchema($tableName)
    {
        if (!isset($this->tableSchemas[$tableName])) {
            $this->tableSchemas[$tableName] = $this->schema->getTableSchema($tableName);
        }
        return $this->tableSchemas[$tableName];
    }
    
    /**
     * @param $tableName
     *
     * @return \string[]
     */
    public function getPrimaryKeys($tableName)
    {
        $tableSchema = $this->getTableSchema($tableName);
        return $tableSchema->primaryKey;
    }
    
    /**
     * @param string $tableName
     *
     * @return array|TableRelation[]
     **/
    public function getRelations($tableName)
    {
        
        $tableSchema = $this->getTableSchema($tableName);
        $relations = [];
        if (!empty($tableSchema->foreignKeys)) {
            foreach ($tableSchema->foreignKeys as $name => $constraints) {
                if(!empty($constraints)){
                    $tableName = array_shift($constraints);
                    $relations[] = new TableRelation($name, $tableName, $constraints);
                }
            }
        }
        return $relations;
    }
    
    /**
     * @param string $tableName
     *
     * @return array|TableIndex[]
     **/
    public function getIndexes($tableName)
    {
        
        $tableSchema = $this->getTableSchema($tableName);
        if ($this->connection->driverName == 'mysql') {
            $schemaIndexes = $this->connection->createCommand('SHOW INDEX FROM [[' . $tableName . ']]')->queryAll();
            return $this->extractMysqlIndexes($schemaIndexes);
        } elseif ($this->connection->driverName == 'pgsql') {
            $schemaIndexes = $this->fetchPqSqlIndexes($tableSchema->schemaName, $tableName);
            return $this->extractPgsqlIndexes($schemaIndexes);
        } elseif (method_exists($this->schema, 'findUniqueIndexes')) {
            $schemaIndexes = call_user_func([$this->schema, 'findUniqueIndexes'], $tableSchema);
            return $this->extractUniqueIndexes($schemaIndexes);
        }
    }
    
    /**
     * @param $schemaName
     * @param $tableName
     *
     * @return array
     */
    protected function fetchPqSqlIndexes($schemaName, $tableName)
    {
        $sql
            = <<<SQL
SELECT
    i.relname as indexname, idx.indisprimary as ispk,  idx.indisunique  as isuniq,
    pg_get_indexdef(idx.indexrelid, k + 1, TRUE) AS columnname
FROM (
  SELECT *, generate_subscripts(indkey, 1) AS k
  FROM pg_index
) idx
INNER JOIN pg_class i ON i.oid = idx.indexrelid
INNER JOIN pg_class c ON c.oid = idx.indrelid
INNER JOIN pg_namespace ns ON c.relnamespace = ns.oid
WHERE  c.relname = :tableName  AND ns.nspname = :schemaName
ORDER BY i.relname, k
SQL;
        return $this->connection->createCommand(
            $sql,
            [
                ':schemaName' => $schemaName,
                ':tableName'  => $tableName,
            ]
        )->queryAll();
    }
    
    /**
     * @param $schemaIndexes
     *
     * @return array|TableIndex[]
     */
    private function extractMysqlIndexes($schemaIndexes)
    {
        $indexes = [];
        if (!empty($schemaIndexes)) {
            $schemaIndexes = ArrayHelper::index($schemaIndexes, null, 'Key_name');
            foreach ($schemaIndexes as $indexName => $data) {
                $cols = ArrayHelper::getColumn(
                    $data,
                    function ($element) {
                        return trim($element['Column_name'], '\'"');
                    },
                    true
                );
                $isUnique = reset($data)['Non_unique'] == 1 ? false : true;
                $indexes[] = new TableIndex($indexName, $cols, $isUnique);
            }
        }
        return $indexes;
    }
    
    /**
     * @param array $schemaIndexes
     *
     * @return array|TableIndex[]
     */
    private function extractPgsqlIndexes($schemaIndexes)
    {
        $indexes = [];
        if (!empty($schemaIndexes)) {
            $schemaIndexes = ArrayHelper::index($schemaIndexes, null, 'indexname');
            foreach ($schemaIndexes as $indexName => $data) {
                    $isPk = reset($data)['ispk']?true:false;
                    if(!$isPk){
                        $cols = ArrayHelper::getColumn(
                            $data,
                            function ($element) {
                                return trim($element['columnname'], '\'"');
                            },
                            true
                        );
                        $isUnique = reset($data)['isuniq'] ? true : false;
                        $indexes[] = new TableIndex($indexName, $cols, $isUnique);
                    }
            }
        }
        return $indexes;
    }
    
    /**
     * @param array $schemaIndexes
     *
     * @return array|TableIndex[]
     */
    private function extractUniqueIndexes($schemaIndexes)
    {
        $indexes = [];
        if (!empty($schemaIndexes)) {
            foreach ($schemaIndexes as $indexName => $columns) {
                $cols = array_walk(
                    $columns,
                    function (&$v) {
                        $v = trim($v, '\'"');
                    }
                );
                $isUnique = 1;
                $indexes[] = new TableIndex($indexName, $cols, $isUnique);
            }
        }
        return $indexes;
    }
    
    /**
     * @param $tableList
     *
     * @return array
     */
    private function extractTablesFromList($tableList)
    {
        $tables = [];
        $list = StringHelper::explode($tableList, ',', true, true);
        if (!empty($list)) {
            foreach ($list as $table) {
                $prepared = $this->findTablesByPattern($table);
                if (!empty($prepared)) {
                    $tables = array_merge($tables, $prepared);
                }
            }
        }
        unset($list);
        return $tables;
    }
    
}