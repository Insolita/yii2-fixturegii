<?php
/**
 * Created by solly [25.05.17 12:44]
 */

namespace insolita\fixturegii\generators;

use insolita\validators\PathValidator;
use yii\gii\CodeFile;
use yii\gii\Generator;
use yii\helpers\StringHelper;

/**
 * class ClassGenerator
 *
 * @package insolita\fixturegii\generators
 */
class ClassGenerator extends Generator
{
    /**
     * @var string
     */
    public $parentClass = 'yii\test\ActiveFixture';
    
    /**
     * @var string
     */
    public $classPath = '@tests/fixtures/';
    
    /**
     * @var string
     */
    public $classNs = 'tests\fixtures\\';
    
    /**
     * @var string
     */
    public $modelClass = '';
    
    /**
     * @var string
     */
    public $fixturePath = '@tests/fixtures/data/user.php';
 
    public $templates = [
        'default'=>'@insolita/fixturegii/templates'
    ];
    /**
     * @return string
     */
    public function getName()
    {
        return 'Active Fixture Class generator';
    }
    
    public function formView()
    {
        $class = new \ReflectionClass($this);
    
        return dirname($class->getFileName()) . '/../forms/class_form.php';
    }
    
    /**
     * @return array
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [
                    ['parentClass', 'classPath', 'classNs', 'modelClass', 'fixturePath'],
                    'required',
                ],
                [['parentClass', 'modelClass'], 'validateClass'],
                [['classNs'], 'validateNewClass'],
                [['classPath'], PathValidator::class,'strictDir'=>true,'writeable'=>true],
                [['fixturePath'],
                 PathValidator::class,
                 'strictFile'=>true,
                 'aliasReplace'=>false,
                 'normalize'=>false,
                 'readable'=>true],
            ]
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
                'parentClass' => 'Base Fixture Class',
                'modelClass'  => 'Model Class',
                'classPath'   => 'Fixture Path Directory',
                'classNs'     => 'FQN Fixture Class Namespace',
                'fixturePath' => 'Path to file with fixture data',
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
                'modelClass'  => 'with full qualified namespace - as app\\models\\User',
                'fixturePath' => 'like @tests/fixtures/data/user.php',
                'classNs'     => 'FQN Active Fixture - as tests\\fixtures\\UserFixture',
            ]
        );
    }
    
    /**
     * @return array
     */
    public function stickyAttributes()
    {
        return ['template', 'parentClass', 'modelClass', 'classPath', 'classNs', 'fixturePath'];
    }
    
    /**
     * @return array
     */
    public function requiredTemplates()
    {
        return ['fixture_class.php'];
    }
 
    /**
     * @return array
     */
    public function generate()
    {
        $files = [];
        $files[] = new CodeFile(
           $this->classPath . '/' . StringHelper::basename($this->classNs) . '.php',
            $this->render('fixture_class.php', [])
        );
        return $files;
    }
}
