Yii2-fixture/template-generator
===============================
![Status](https://travis-ci.org/Insolita/yii2-fixturegii.svg?branch=master)
![Latest Stable Version](https://img.shields.io/packagist/v/insolita/yii2-fixturegii.svg)
[![Total Downloads](https://img.shields.io/packagist/dt/insolita/yii2-fixturegii.svg)](https://packagist.org/packages/insolita/yii2-fixturegii.svg)
![License](https://img.shields.io/packagist/l/insolita/yii2-fixturegii.svg)

gii fixture helper - generate fixture classes; faker templates; fixture data files from table
support bulk template and data-file generations

Installation
------------


```
php composer.phar require-dev --prefer-dist insolita/yii2-fixturegii "~1.0"
```

or add

```
"insolita/yii2-fixturegii": "~1.0"
```

to the require-dev section of your `composer.json` file.


Usage
-----
Go to gii and use new Generators
**Suggest:** define '@test' or '@fixture' alias for quick path setup
Customize
---------
Set up in gii config sections
```php
  $config['modules']['gii'] = [
          'class' => 'yii\gii\Module',
          'generators' => [
              //...
              'fixtureClass'=>[
                  'class'=>\insolita\fixturegii\generators\ClassGenerator::class,
                  'templates'=>[
                           //add your custom
                  ]
              ],
              'fixtureData'=>[
                  'class'=>\insolita\fixturegii\generators\DataGenerator::class,
                  'tableResolverClass'=>'You can set own implementation',
                  'templates'=>[
                        //add your custom
                  ]
              ],
              'fixtureTemplate'=>[
              'class'=>\insolita\fixturegii\generators\TemplateGenerator::class,
                'tableResolverClass'=>'You can set own implementation',
                'columnResolverClass'=>'You can set own implementation',
                'templates'=>[
                        //add your custom
                ]
              ],
          ]
          //...
      ];
```

