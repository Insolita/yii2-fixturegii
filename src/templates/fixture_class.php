<?php
/**
 * The following variables are available in this view:
 *  @var \insolita\fixturegii\generators\ClassGenerator $generator
 */
use yii\helpers\StringHelper;

echo "<?php\n";
?>
use <?=ltrim($generator->parentClass,'\\')?>;

class <?=StringHelper::basename($generator->classNs)?> extends <?=StringHelper::basename($generator->parentClass)?>

{
      public $modelClass = '<?=$generator->modelClass?>';
      public $dataFile = '<?=$generator->fixturePath?>';
      public $depends = [];
}
