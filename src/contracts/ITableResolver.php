<?php
/**
 * Created by solly [27.05.17 1:12]
 */

namespace insolita\fixturegii\contracts;

use insolita\fixturegii\objects\TableIndex;
use insolita\fixturegii\objects\TableRelation;
use yii\db\TableSchema;

/**
 * Class ITableResolver
 *
 * @package insolita\fixturegii\services
 */
interface ITableResolver
{
    /**
     * @param string $tableList
     * @param string $ignoreList
     */
    public function extractTablesFromMaskedList($tableList, $ignoreList);
    
    /**
     * @param $tableName
     *
     * @return mixed
     */
    public function getTableCaption($tableName);
    
    /**
     * @param $tableName
     *
     * @return string
     */
    public function getTableAlias($tableName);
    
    /**
     * @param string $tablePattern
     *
     * @return array
     **/
    public function findTablesByPattern($tablePattern);
    
    /**
     * @param $tableName
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getTableData($tableName, $limit, $offset);
    
    /**
     * @return array
     **/
    public function getTableNames();
    
    /**
     * @param $tableName
     *
     * @return TableSchema
     */
    public function getTableSchema($tableName);
    
    /**
     * @param $tableName
     *
     * @return \string[]
     */
    public function getPrimaryKeys($tableName);
    
    /**
     * @param string $tableName
     *
     * @return array|TableRelation[]
     **/
    public function getRelations($tableName);
    
    /**
     * @param string $tableName
     *
     * @return array|TableIndex[]
     **/
    public function getIndexes($tableName);
}