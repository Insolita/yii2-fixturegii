Yii2-fixture/template-generator
======================
quick way generate fixture templates for all tables based on table schema, or fixtures data based on tables data
Ability to set multiple tables with mask

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

First, you must specify composer  'minimum-stability' to 'dev' in root section. Like that:
```
...
"minimum-stability": "dev",
"prefer-stable": true,
...
```

See more about 'minimum-stability': https://getcomposer.org/doc/04-schema.md#minimum-stability

Either run

```
php composer.phar require-dev --prefer-dist insolita/yii2-fixturegii "*"
```

or add

```
"insolita/yii2-fixturegii": "*"
```

to the require-dev section of your `composer.json` file.


Usage
-----

Add 
```
Yii::setAlias('@tests', dirname(__DIR__) . '/tests');
```
to your config/web.php

Go to gii, choose FixtureTemplateGenerator, set needed tables or "*" for all, go to setted templatePath,
correct as you want and run php console/yii fixture/generate-all
