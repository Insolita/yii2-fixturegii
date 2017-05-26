<?php
/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var \insolita\fixturegii\generators\ClassGenerator $generator
 */
echo $form->field($generator, 'modelClass');
echo $form->field($generator, 'fixturePath');
?>
<div class="row">
    <div class="col-md-6"><?=$form->field($generator, 'classNs')?></div>
    <div class="col-md-6"><?=$form->field($generator, 'classPath')?></div>
</div>
<?php echo $form->field($generator, 'parentClass');?>
