yii2-base-models
================
The Base Models for Yii 2 Applications

[![Latest Stable Version](https://poser.pugx.org/rhosocial/yii2-base-models/v/stable.png)](https://packagist.org/packages/rhosocial/yii2-base-models)
[![License](https://poser.pugx.org/rhosocial/yii2-base-models/license)](https://packagist.org/packages/rhosocial/yii2-base-models)
[![Reference Status](https://www.versioneye.com/php/rhosocial:yii2-base-models/reference_badge.svg)](https://www.versioneye.com/php/rhosocial:yii2-base-models/references)
[![Build Status](https://img.shields.io/travis/rhosocial/yii2-base-models.svg)](http://travis-ci.org/rhosocial/yii2-base-models)
[![Dependency Status](https://www.versioneye.com/php/rhosocial:yii2-base-models/dev-master/badge.png)](https://www.versioneye.com/php/rhosocial:yii2-base-models/dev-master)
[![Code Coverage](https://scrutinizer-ci.com/g/rhosocial/yii2-base-models/badges/coverage.png)](https://scrutinizer-ci.com/g/rhosocial/yii2-base-models/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/rhosocial/yii2-base-models/badges/quality-score.png)](https://scrutinizer-ci.com/g/rhosocial/yii2-base-models/)
[![Code Climate](https://img.shields.io/codeclimate/github/rhosocial/yii2-base-models.svg)](https://codeclimate.com/github/rhosocial/yii2-base-models)

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).
Either run

```
php composer.phar require --prefer-dist rhosocial/yii2-base-models "*"
```

or add

```
"rhosocial/yii2-base-models": "*"
```

to the require section of your `composer.json` file.

If you want to use Redis or MongoDB ActiveRecord, please add
```
"yiisoft/yii2-redis": "*"
```
or
```
"yiisoft/yii2-mongodb": "~2.1.0"
```
to the require section of your `composer.json` file by yourself.

Note: The MongoDB models need PHP 5.6 or above.

Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
use rhosocial\base\models\models\BaseEntityModel;

class Example extends BaseEntityModel
{
...
}
```

further detailed usage seen in code file notes.

Contact Us
----------

[![Join the chat at https://gitter.im/rhosocial/yii2-base-models](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/rhosocial/yii2-base-models?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

If you have any problems or good ideas about yii2-base-models, please discuss there, or send an email to i@vistart.me. Thank you!

If you want to send an email with your issues, please briefly introduce yourself first, for instance including your title and github homepage.

[![yii2-base-models](https://img.shields.io/badge/Powered_by-rhosocial-green.svg?style=flat)](https://dev.rho.social/products/yii2-base-models)