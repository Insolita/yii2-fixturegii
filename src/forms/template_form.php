<?php
/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var \insolita\fixturegii\generators\TemplateGenerator $generator
 */

echo $form->field($generator, 'tableName');
echo $form->field($generator, 'tableIgnore');
echo $form->field($generator, 'db');
echo $form->field($generator, 'tplPath');