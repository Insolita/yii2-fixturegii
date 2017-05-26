<?php
/**
 * The following variables are available in this view:
 */
/**
 *  @var array $tableColumns
 *  @var \insolita\fixturegii\generators\TemplateGenerator $generator
 */

echo "<?php\n";
?>
/**
* @var $faker \Faker\Generator
* @var $index integer
*/
return [
<?php foreach($tableColumns as $name=>$fakeString):?>
    '<?=$name?>'=> <?=$fakeString;?>,
<?php endforeach;?>
];