<?php
/**
 * The following variables are available in this view:
 */
/**
 *  @var array $tableColumns
 *  @var insolita\fixturegii\gii\FixtureTemplateGenerator $generator
 */

echo "<?php\n";
?>
/**
* @var $faker \Faker\Generator
* @var $index integer
*/
return [
<?php foreach($tableColumns as $name=>$fakeval):?>
    '<?=$name?>'=> <?=$fakeval;?>,
<?php endforeach;?>
];