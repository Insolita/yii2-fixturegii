<?php
/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var \insolita\fixturegii\generators\DataGenerator $generator
 */

echo $form->field($generator, 'tableName');
echo $form->field($generator, 'tableIgnore');
echo $form->field($generator, 'db');
echo $form->field($generator, 'fixturePath');
?>
<div class="row">
    <div class="col-md-6"><?=$form->field($generator, 'dataLimit')?></div>
    <div class="col-md-6"><?=$form->field($generator, 'dataOffset')?></div>
</div>
