<?php
/**
 * Created by solly [25.05.17 12:35]
 */

namespace insolita\fixturegii\generators;

use insolita\fixturegii\contracts\IFakerColumnResolver;
use insolita\fixturegii\services\FakerColumnResolver;
use insolita\fixturegii\services\TableResolver;
use insolita\validators\PathValidator;
use yii\gii\CodeFile;
use yii\gii\Generator;

/**
 * Class TemplateGenerator
 *
 * @package insolita\fixturegii\generators\ftemplate
 */
class TemplateGenerator extends Generator
{
    /**
     * @var string
     */
    public $db = 'db';
    
    /**
     * @var string
     */
    public $tplPath = '@tests/codeception/common/fdata/templates';
    
    /**
     * @var
     */
    public $tableName;
    
    /**
     * @var string
     */
    public $tableIgnore = '';
    
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
    public $columnResolverClass = FakerColumnResolver::class;
    
    /**
     * @var string
     */
    public $tableResolverClass = TableResolver::class;
    
    /**
     * @return string
     */
    public function getName()
    {
        return 'Faker Fixture Templates';
    }
    
    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Generate fixture templates for FakerController';
    }
    
    /**
     * @return string
     */
    public function formView()
    {
        $class = new \ReflectionClass($this);
        
        return dirname($class->getFileName()) . '/../forms/template_form.php';
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
     * @return array
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [['tableName', 'tableIgnore', 'db', 'tplPath'], 'trim'],
                [['db', 'tableName', 'tplPath'], 'required'],
                [['tplPath'], PathValidator::class, 'strictDir' => true, 'writeable' => true],
                [['db'], 'match', 'pattern' => '/^\w+$/', 'message' => 'Only word characters are allowed.'],
                [
                    ['tableName', 'tableIgnore'],
                    'match',
                    'pattern' => '/[^\w\*_\,\-\s]/',
                    'not'     => true,
                    'message' => 'Only word characters, underscore, comma,and also an asterisk are allowed.',
                ],
                [['db'], 'string'],
            ]
        );
    }
    
    /**
     * @inheritdoc
     */
    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes(), ['db', 'tplPath', 'tableIgnore']);
    }
    
    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return ['fixture_tpl.php'];
    }
    
    /**
     * @return array
     */
    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'db'          => 'Db component name',
                'tableName'   => 'Table Names',
                'tableIgnore' => 'Ignored tables',
                'tplPath'     => 'Path where templates will stored',
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
                'tableIgnore' => 'You can separate some table names by comma, for skip generations ',
                'tplPath'     => 'Path to directory with fixture templates',
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
                $tableSchema = $resolver->getTableSchema($tableName);
                $tableCaption = $resolver->getTableCaption($tableName);
                $tableRelations = $resolver->getRelations($tableName);
                $tableIndexes = $resolver->getIndexes($tableName);
                $columnResolver = $this->createColumnResolver($tableSchema->columns, $tableRelations, $tableIndexes);
                $tableColumns = $columnResolver->buildFakerColumnsData();
                $params = compact(
                    'tableColumns'
                );
                $files[] = new CodeFile(
                    \Yii::getAlias($this->tplPath) . '/' . $tableCaption . '.php',
                    $this->render('fixture_tpl.php', $params)
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
    
    /**
     * @param  array|\yii\db\ColumnSchema[]                      $columns
     * @param array|\insolita\fixturegii\objects\TableRelation[] $relations
     * @param array|\insolita\fixturegii\objects\TableIndex[]    $indexes
     *
     * @return IFakerColumnResolver
     */
    protected function createColumnResolver($columns, $relations = [], $indexes = [])
    {
        return \Yii::createObject(
            $this->columnResolverClass,
            [
                $columns,
                $relations,
                $indexes,
            ]
        );
    }
}
