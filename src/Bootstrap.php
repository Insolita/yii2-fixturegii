<?php

namespace insolita\fixturegii;

use insolita\fixturegii\generators\ClassGenerator;
use insolita\fixturegii\generators\DataGenerator;
use insolita\fixturegii\generators\TemplateGenerator;
use yii\base\Application;
use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{
    /**
     * Bootstrap method to be called during application bootstrap stage.
     *
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        if ($app->hasModule('gii')) {
            if (!isset($app->getModule('gii')->generators['fixtureClass'])) {
                $app->getModule('gii')->generators['fixtureClass'] = ClassGenerator::class;
            }
            if (!isset($app->getModule('gii')->generators['fixtureData'])) {
                $app->getModule('gii')->generators['fixtureData'] = DataGenerator::class;
            }
            if (!isset($app->getModule('gii')->generators['fixtureTemplate'])) {
                $app->getModule('gii')->generators['fixtureTemplate'] = TemplateGenerator::class;
            }
        }
    }
}
