<?php
/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var yii\gii\generators\form\Generator $generator
 */

echo $form->field($generator, 'tableName');
echo $form->field($generator, 'tableIgnore');
echo $form->field($generator, 'db');
echo $form->field($generator, 'templatePath');
echo $form->field($generator, 'genmode')->dropDownList([0=>'templates by table schema',1=>'fixtures by table data']);
echo $form->field($generator, 'datalimit');