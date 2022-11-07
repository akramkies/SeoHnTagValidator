# SeoHnTagValidator
[![License](https://poser.pugx.org/globalis/chargebee-php-sdk/license)](https://github.com/akramkies/SeoHnTagValidator/blob/main/LICENSE.md)

Overview
------------

This package provides an API client for any website url.

it allows to verify the structure of hN via two methods:
- **validateUrl :** this method makes it possible to check if the structuring of hN is valid or not. by displaying the errors found in order to correct them manually.
- **validateWebSite:** this method is based on the validateUrl method and which makes it possible to check the structuring of hNs in all the html pages of a website

Installation
------------

```bash
composer require globalis/seohntagvalidator
```

Basic usage
------------

```php
<?php
use \Globalis\SeoHnTagValidator\SeoHnTagValidator;


$validator  = new SeoHnTagValidator();
//calling the method validateUrl()
$res = $validator->validateUrl('https://www.example.com');
print_r($res);

/* 
   calling the method validateWebSite()
   $concurrent default 3
   $errors show only urls wih errors default false 
*/
$res = $validator->validateWebSite('https://www.example.com',$errors[OPTIONAL],$concurrent[OPTIONAL]);
print_r($res);


```

CLI usage
------------
```bash
# calling validateUrl method
php run hN:validateUrl  https://www.example.com

# calling validateWebSite method
php run hN:validateWebSite  --only-errors=true   --concurrent-requests=6 https://www.example.com
```