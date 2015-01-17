<?php
/**
 * Created by PhpStorm.
 * User: Insolita
 * Date: 08.12.14
 * Time: 8:03
 */
namespace insolita\fixturegii\gii;

use Yii;
use yii\db\Connection;
use yii\db\TableSchema;
use yii\gii\CodeFile;
use yii\helpers\VarDumper;

class FixtureTemplateGenerator extends \yii\gii\Generator
{


    public $db = 'db';
    public $templatePath = '@tests/codeception/common/fixtures/templates';
    public $tableName;
    public $tableIgnore = '';

    private $_ignoredTables = [];
    private $_tables = [];

    /**
     * @inheritdoc
     */
    public function getName()
    {

        return 'FixtureTemplateGenerator';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'This generator generates fixture templates based on tables';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(), [
                [['db', 'tableName', 'tableIgnore'], 'filter', 'filter' => 'trim'],
                [['db', 'tableName'], 'required'],
                [['db'], 'match', 'pattern' => '/^\w+$/', 'message' => 'Only word characters are allowed.'],
                [
                    ['tableName','tableIgnore'], 'match', 'pattern' =>'/[^\w\*_\,\-\s]/','not'=>true,
                    'message' => 'Only word characters, underscore, comma,and optionally an asterisk are allowed.'
                ],
                [['db'], 'validateDb'],
                [['tableName'], 'validateTableName'],
                ['templatePath', 'safe'],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(), [
                'db' => 'Database Connection ID',
                'tableName' => 'Table Name',
                'tableIgnore' => 'Ignored tables',
                'templatePath' => 'Template Path',
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return array_merge(
            parent::hints(), [
                'db' => 'This is the ID of the DB application component.',
                'tableName' => 'Use "*" for all table, mask support - as "tablepart*", or you can separate table names by comma ',
                'tableIgnore' => 'You can separate some table names by comma, for ignor ',
                'templatePath' => 'Path for you Fixture templates'
            ]
        );
    }

    /**
     * Validates the [[db]] attribute.
     */
    public function validateDb()
    {
        if (!Yii::$app->has($this->db)) {
            $this->addError('db', 'There is no application component named "db".');
            return false;
        } elseif (!Yii::$app->get($this->db) instanceof Connection) {
            $this->addError('db', 'The "db" application component must be a DB connection instance.');
            return false;
        }
        return true;
    }

    /**
     * Validates the [[tableName]] attribute.
     */
    public function validateTableName()
    {
        $tables=$this->prepareTables();

        if (empty($tables)) {
            $this->addError('tableName', "Table '{$this->tableName}' does not exist, or all tables was ignored");
            return false;
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function autoCompleteData()
    {
        $db = $this->getDbConnection();
        if ($db !== null) {
            return [
                'tableName' => function () use ($db) {
                    return $db->getSchema()->getTableNames();
                },
            ];
        } else {
            return [];
        }
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return ['fixturetpl.php'];
    }

    /**
     * @inheritdoc
     */
    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes(), ['db', 'templatePath', 'tableIgnore']);
    }

    public function getIgnoredTables()
    {
        return $this->_ignoredTables;
    }

    public function getTables()
    {
        return $this->_tables;
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $files = $tableRelations = $tableList = [];
        $db = $this->getDbConnection();
        foreach ($this->getTables() as $tableName) {
            $tableSchema = $db->getTableSchema($tableName);
            $tableCaption = $this->getTableCaption($tableName);
            $tableColumns = $this->columnsBySchema($tableSchema);
            $params = compact(
                'tableColumns'
            );
            $files[] = new CodeFile(
                Yii::getAlias($this->templatePath) . '/' . $tableCaption . '.php',
                $this->render('fixturetpl.php', $params)
            );
        }


        return $files;
    }


    public function columnsBySchema($schema)
    {
        $cols = [];
        /**@var TableSchema $schema * */
        foreach ($schema->columns as $column) {
            $type = $this->getColumnType($column, $schema);
            $cols[$column->name] = $type;
        }
        return $cols;
    }


    public function getColumnType($col, &$schema)
    {
        $coldata = '';
        $related=$this->findRelatedAttrs($schema);
        /**@var \yii\db\ColumnSchema $col * */
        if ($col->autoIncrement || in_array($col->name, $related)) {
            $coldata = '$index';
        } elseif (strpos($col->dbType, 'set(') !== false) {
            preg_match_all('#set\((.+)\)#', $col->dbType, $matches);
            if (!empty($matches) && isset($matches[1][0])) {
                $coldata = '$faker->randomElement($array = array (' . $matches[1][0]. '))';
            } else {
                $coldata = null;
            }

        } elseif (strpos($col->dbType, 'enum(') !== false) {
            preg_match_all('#enum\((.+)\)#', $col->dbType, $matches);
            if (!empty($matches) && isset($matches[1][0])) {
                $coldata = '$faker->randomElement($array = array (' . $matches[1][0]. '))';
            } else {
                $coldata = null;
            }
        } elseif ($col->dbType === 'tinyint(1)') {
            $coldata = '$faker->randomElement($array = array (0,1))';
        }elseif ($col->type === 'string') {
            $coldata = $this->getFakerString($col->size, $col->name);
        }elseif ($col->type === 'text') {
            $coldata = $col->size?'$faker->text($maxNbChars = '.($col->size-15).')':'$faker->text(300)';
        }elseif ($col->type === 'integer' || $col->type === 'smallint' || $col->type === 'mediumint' || $col->type === 'bigint') {
            $coldata = $this->getFakerInt($col->size, $col->name);
        }elseif ($col->type === 'smallint') {
            $coldata = $this->getFakerInt($col->size, $col->name);
        } elseif ($col->type === 'timestamp') {
            $coldata = '$faker->unixTime()';
        } elseif ($col->type === 'datetime') {
            $coldata = '$faker->dateTime()';
        } elseif ($col->type === 'date') {
            $coldata = '$faker->date()';
        }  elseif ($col->type === 'time') {
            $coldata = '$faker->time()';
        }elseif ($col->type === 'decimal' || $col->type==='float') {
            $coldata = '$faker->randomFloat($nbMaxDecimals='.$col->scale.', $max='.$col->size.')';
        }else {
            $coldata = 'TYPE_' . strtoupper($col->type);
        }

        return $coldata;
    }

    public function findRelations($schema)
    {
        /**@var TableSchema $schema * */
        $rels = [];
        if (!empty($schema->foreignKeys)) {
            foreach ($schema->foreignKeys as $i => $constraint) {
                foreach ($constraint as $pk => $fk) {
                    if (!$pk) {
                        $rels[$i]['ftable'] = $fk;
                    } else {
                        $rels[$i]['pk'] = $pk;
                        $rels[$i]['fk'] = $fk;
                    }
                }
            }
        }
        //return [VarDumper::dumpAsString($schema->foreignKeys)];
        return $rels;
    }

    public function findRelatedAttrs($schema)
    {
        /**@var TableSchema $schema * */
        $rels = [];
        if (!empty($schema->foreignKeys)) {
            foreach ($schema->foreignKeys as $i => $constraint) {
                foreach ($constraint as $pk => $fk) {
                    if($pk){
                        $rels[]=$pk;
                    }
                }
            }
        }
        //return [VarDumper::dumpAsString($schema->foreignKeys)];
        return $rels;
    }

    public function getFakerString($size, $colname)
    {
        $colname=strtolower($colname);
        $colname=str_replace('_','',$colname);

        if(strpos($colname,'username')!==false || strpos($colname,'nick')!==false || strpos($colname,'user')!==false){
            return '$faker->userName';
        }elseif(strpos($colname,'firstname')!==false){
            return '$faker->firstName()';
        }elseif(strpos($colname,'lastname')!==false){
            return '$faker->lastName()';
        }elseif(strpos($colname,'name')!==false){
            return '$faker->name';
        }elseif(strpos($colname,'title')!==false){
            return '$faker->sentence($nbWords = 3)';
        }elseif(strpos($colname,'mail')!==false){
            return '$faker->email';
        }elseif(strpos($colname,'slug')!==false || strpos($colname,'alias')!==false){
            return '$faker->slug';
        }elseif(strpos($colname,'url')!==false || strpos($colname,'link')!==false ){
            return '$faker->url';
        }elseif(strpos($colname,'ip')!==false ){
            return '$faker->ipv4';
        }elseif(strpos($colname,'avatar')!==false || strpos($colname,'image')!==false || strpos($colname,'img')!==false){
            return '$faker->image()';
        }elseif(strpos($colname,'file')!==false || strpos($colname,'path')!==false){
            return '$faker->file()';
        }elseif($size<15){
            return '$faker->word';
        }else{
            return ($size)?'$faker->text('.($size-1).')':'$faker->paragraph()';
        }
    }
    public function getFakerInt($size, $colname)
    {
        $colname=strtolower($colname);
        $colname=str_replace('_','',$colname);
        if($size==11 && (
                strpos($colname,'create')!==false
                || strpos($colname,'update')!==false
                || strpos($colname,'last')!==false
                || strpos($colname,'modif')!==false
                || strpos($colname,'time')!==false
                || strpos($colname,'date')!==false
            )){
            return '$faker->unixTime()';
        }else{
            return '$faker->randomNumber($nbDigits = '.$size.')';
        }
    }


    public function getTableCaption($tableName)
    {
        $db = $this->getDbConnection();
        return str_replace($db->tablePrefix, '', strtolower($tableName));
    }

    public function getTableAlias($tableCaption)
    {
        return '{{%' . $tableCaption . '}}';
    }

    public function prepareIgnored()
    {
        $ignors = [];
        if ($this->tableIgnore) {
            if (strpos($this->tableIgnore, ',') !== false) {
                $ignors = explode(',', $this->tableIgnore);
            } else {
                $ignors[] = $this->tableIgnore;
            }
        }
        if (!empty($ignors)) {
            foreach ($ignors as $ignoredTable) {
                $prepared = $this->prepareTableName($ignoredTable);
                if (!empty($prepared)) {
                    $this->_ignoredTables=array_merge($this->_ignoredTables, $prepared);
                }
            }
        }
        return $this->_ignoredTables;
    }

    public function prepareTableName($tableName)
    {
        $prepared = [];
        $tableName=trim($tableName);
        $db = $this->getDbConnection();
        if ($db === null) {
            return $prepared;
        }
        if ($tableName == '*') {
            foreach ($db->schema->getTableNames() as $table) {
                $prepared[] = $table;
            }
        } elseif (strpos($tableName, '*') !== false) {
            $schema = '';
            $pattern = '/^' . str_replace('*', '\w+', $tableName) . '$/';

            foreach ($db->schema->getTableNames($schema) as $table) {
                if (preg_match($pattern, $table)) {
                    $prepared[] = $table;
                }
            }
        } elseif (($table = $db->getTableSchema($tableName, true)) !== null) {
            $prepared[] = $tableName;
        }
        return $prepared;
    }


    /**
     * @return array the table names that match the pattern specified by [[tableName]].
     */
    public function prepareTables()
    {
        $tables = [];
        $this->prepareIgnored();
        if ($this->tableName) {
            if (strpos($this->tableName, ',') !== false) {
                $tables = explode(',', $this->tableName);
            } else {
                $tables[] = $this->tableName;
            }
        }
        if (!empty($tables)) {
            foreach ($tables as $goodTable) {
                $prepared = $this->prepareTableName($goodTable);
                if (!empty($prepared)) {
                    $this->_tables=array_merge($this->_tables, $prepared);
                }
           }
            foreach($this->_tables as $i=>$t){
                if(in_array($t, $this->_ignoredTables)){
                    unset($this->_tables[$i]);
                }
            }
        }

        return $this->_tables;
    }


    /**
     * @return Connection the DB connection as specified by [[db]].
     */
    protected function getDbConnection()
    {
        return Yii::$app->{$this->db};
    }

    /**
     * Checks if any of the specified columns is auto incremental.
     *
     * @param  \yii\db\TableSchema $table the table schema
     * @param  array               $columns columns to check for autoIncrement property
     *
     * @return boolean             whether any of the specified columns is auto incremental.
     */
    protected function isColumnAutoIncremental($table, $columns)
    {
        foreach ($columns as $column) {
            if (isset($table->columns[$column]) && $table->columns[$column]->autoIncrement) {
                return true;
            }
        }

        return false;
    }

} 