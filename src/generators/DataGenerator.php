<?php
/**
 * Created by solly [25.05.17 15:59]
 */

namespace insolita\fixturegii\generators;

use insolita\fixturegii\services\TableResolver;
use insolita\validators\PathValidator;
use yii\gii\CodeFile;
use yii\gii\Generator;
use yii\helpers\ArrayHelper;

class DataGenerator extends Generator
{
    public $tableName;
    
    public $tableIgnore;
    
    public $db='db';
    
    public $fixturePath;
    
    public $dataLimit = 5;
    
    public $dataOffset = 0;
    
    public $templates = [
        'default'=>'@insolita/fixturegii/templates'
    ];
    
    /**
     * @var string
     */
    public $tableResolverClass = TableResolver::class;
    
    public function getName()
    {
        return 'Fixture Data From Db';
    }
    
    public function getDescription()
    {
        return 'Generate fixture data files from db';
    }
    public function formView()
    {
        $class = new \ReflectionClass($this);
        
        return dirname($class->getFileName()) . '/../forms/data_form.php';
    }
    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return ['fixture_data.php'];
    }
    
    /**
     * @inheritdoc
     */
    public function stickyAttributes()
    {
        return array_merge(
            parent::stickyAttributes(),
            ['db',  'tableIgnore', 'fixturePath', 'dataLimit']
        );
    }
    
    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'db'          => 'Database Component Name',
                'tableName'   => 'Table Names',
                'tableIgnore' => 'Ignored tables',
                'fixturePath' => 'Path to directory where fixture data will stored',
                'dataLimit'   => 'Limit rows fetched from each table',
                'dataOffset'  => 'Offset Row Count',
            ]
        );
    }
    
    public function attributeHints()
    {
        return array_merge(
            parent::attributeHints(),
            [
                'tableName'   => 'Use "*" for all tables or you can separate table names by comma
                ,also masks supported - like as "tablepart*"',
                'tableIgnore' => 'You can separate some table names by comma, for skip ',
            ]
        );
    }
    
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [['tableName', 'tableIgnore', 'db', 'fixturePath'], 'trim'],
                [['db', 'tableName', 'fixturePath'], 'required'],
                [['fixturePath'], PathValidator::class, 'strictDir' => true, 'writeable' => true],
                [['db'], 'match', 'pattern' => '/^\w+$/', 'message' => 'Only word characters are allowed.'],
                [
                    ['tableName', 'tableIgnore'],
                    'match',
                    'pattern' => '/[^\w\*_\,\-\s]/',
                    'not'     => true,
                    'message' => 'Only word characters, underscore, comma,and also an asterisk are allowed.',
                ],
                [['db'], 'string'],
                [['dataLimit'], 'integer', 'min' => 1],
                [['dataOffset'], 'integer', 'min' => 0],
                [['dataLimit'], 'default', 'value' => 5],
                [['dataOffset'], 'default', 'value' => 0],
            ]
        );
    }
    
    /**
     * @return array|\yii\gii\CodeFile[]
     */
    public function generate()
    {
        $files = [];
        $resolver = $this->createTableResolver();
        $tables = $resolver->extractTablesFromMaskedList($this->tableName, $this->tableIgnore);
        if (!empty($tables)) {
            foreach ($tables as $tableName) {
                $tableCaption = $resolver->getTableCaption($tableName);
                $tableData = $resolver->getTableData($tableName, $this->dataLimit, $this->dataOffset);
                if(empty($tableData)){
                    $data = [];
                    $columns = $resolver->getTableSchema($tableName)->columns;
                    foreach ($columns as $column){
                        $data[$column->name] = $column->allowNull?null:'';
                    }
                    $tableData = [$data];
                }
                $params = ['data' => $tableData, 'tableCaption' => $tableCaption];
                $files[] = new CodeFile(
                    \Yii::getAlias($this->fixturePath) . '/' . $tableCaption . '.php',
                    $this->render('fixture_data.php', $params)
                );
            }
        }
        return $files;
    }
    
    /**
     * @return TableResolver
     */
    protected function createTableResolver()
    {
        return \Yii::createObject(
            $this->tableResolverClass,
            [
                \Yii::$app->get($this->db),
            ]
        );
    }
}
