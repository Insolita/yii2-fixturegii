<?php
/**
 * The following variables are available in this view:
 */
/**
 *  @var array $data
 *  @var string $tableCaption
 *  @var insolita\fixturegii\gii\FixtureTemplateGenerator $generator
 */
echo "<?php\n";
?>
return [
<?php foreach($data as $i=>$row):?>
    '<?=$tableCaption.($i+1)?>'=>[
    <?php foreach($row as $name=>$val):?>
    '<?=$name?>'=> '<?=$val;?>',
    <?php endforeach;?>
    ],
<?php endforeach;?>
];