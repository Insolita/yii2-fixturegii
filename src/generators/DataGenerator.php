<?php
/**
 * Created by solly [25.05.17 15:59]
 */

namespace insolita\fixturegii\generators;

use insolita\fixturegii\services\TableResolver;
use insolita\validators\PathValidator;
use yii\gii\CodeFile;
use yii\gii\Generator;

/**
 * Class DataGenerator
 *
 * @package insolita\fixturegii\generators
 */
class DataGenerator extends Generator
{
    /**
     * @var
     */
    public $tableName;
    
    /**
     * @var
     */
    public $tableIgnore;
    
    /**
     * @var string
     */
    public $db = 'db';
    
    /**
     * @var
     */
    public $fixturePath;
    
    /**
     * @var int
     */
    public $dataLimit = 5;
    
    /**
     * @var int
     */
    public $dataOffset = 0;
    
    /**
     * @var array
     */
    public $templates
        = [
            'default' => '@insolita/fixturegii/templates',
        ];
    
    /**
     * @var string
     */
    public $tableResolverClass = TableResolver::class;
    
    /**
     * @return string
     */
    public function getName()
    {
        return 'Fixture Data From Db';
    }
    
    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Generate fixture data files from db';
    }
    
    /**
     * @return string
     */
    public function formView()
    {
        $class = new \ReflectionClass($this);
        
        return dirname($class->getFileName()) . '/../forms/data_form.php';
    }
    
    /**
     * Returns the root path to the default code template files.
     * The default implementation will return the "templates" subdirectory of the
     * directory containing the generator class file.
     *
     * @return string the root path to the default code template files.
     */
    public function defaultTemplate()
    {
        $class = new \ReflectionClass($this);
        
        return dirname($class->getFileName()) . '/../templates';
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
            ['db', 'tableIgnore', 'fixturePath', 'dataLimit']
        );
    }
    
    /**
     * @return array
     */
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
    
    /**
     * @return array
     */
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
    
    /**
     * @return array
     */
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
                if (empty($tableData)) {
                    $data = [];
                    $columns = $resolver->getTableSchema($tableName)->columns;
                    foreach ($columns as $column) {
                        $data[$column->name] = $column->allowNull ? null : '';
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
     * @return \insolita\fixturegii\contracts\ITableResolver
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
