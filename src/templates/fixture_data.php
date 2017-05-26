<?php
/**
 * The following variables are available in this view:
 */
/**
 *  @var array                                                     $data
 *  @var string                                                    $tableCaption
 *  @var \insolita\fixturegii\generators\DataGenerator $generator
 */
echo "<?php\n";
?>
return [
<?php foreach($data as $index=>$row):?>
    '<?=$tableCaption.($index+1)?>'=>[
    <?php foreach($row as $name=>$val):?>
    '<?=$name?>'=> '<?= addslashes($val);?>',
    <?php endforeach;?>
    ],
<?php endforeach;?>
];
