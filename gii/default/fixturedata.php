<?php
/**
 * The following variables are available in this view:
 */
/**
 *  @var array $data
 *  @var insolita\fixturegii\gii\FixtureTemplateGenerator $generator
 */
echo "<?php\n";
?>
return [
<?php foreach($data as $i=>$row):?>
    [
    <?php foreach($row as $name=>$val):?>
    '<?=$name?>'=> '<?=$val;?>',
    <?php endforeach;?>
    ],
<?php endforeach;?>
];