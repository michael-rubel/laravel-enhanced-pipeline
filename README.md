# Laravel Enhanced Pipeline
[![Latest Version on Packagist](https://img.shields.io/packagist/v/michael-rubel/laravel-enhanced-pipeline.svg?style=flat-square&logo=packagist)](https://packagist.org/packages/michael-rubel/laravel-enhanced-pipeline)
[![Total Downloads](https://img.shields.io/packagist/dt/michael-rubel/laravel-enhanced-pipeline.svg?style=flat-square&logo=packagist)](https://packagist.org/packages/michael-rubel/laravel-enhanced-pipeline)
[![Code Quality](https://img.shields.io/scrutinizer/quality/g/michael-rubel/laravel-enhanced-pipeline.svg?style=flat-square&logo=scrutinizer)](https://scrutinizer-ci.com/g/michael-rubel/laravel-enhanced-pipeline/?branch=main)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/michael-rubel/laravel-enhanced-pipeline.svg?style=flat-square&logo=scrutinizer)](https://scrutinizer-ci.com/g/michael-rubel/laravel-enhanced-pipeline/?branch=main)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/michael-rubel/laravel-enhanced-pipeline/run-tests/main?style=flat-square&label=tests&logo=github)](https://github.com/michael-rubel/laravel-enhanced-pipeline/actions)
[![PHPStan](https://img.shields.io/github/workflow/status/michael-rubel/laravel-enhanced-pipeline/phpstan/main?style=flat-square&label=larastan&logo=laravel)](https://github.com/michael-rubel/laravel-enhanced-pipeline/actions)

Laravel pipelines with transaction support and handy additional methods.

---

The package requires PHP `^8.x` and Laravel `^8.71` or `^9.0`.

## #StandWithUkraine
[![SWUbanner](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/banner2-direct.svg)](https://github.com/vshymanskyy/StandWithUkraine/blob/main/docs/README.md)

## Installation
Install the package using composer:
```bash
composer require michael-rubel/laravel-enhanced-pipeline
```

## Usage
Import modified pipeline to your class:
```php
use MichaelRubel\EnhancedPipeline\Pipeline::class;
```

Then use the same way as an original one:
```php
Pipeline::make()
    ->withTransaction()
    ->send($data)
    ->through([
        // your pipes
    ])
    ->onFailure(function ($data, $exception, $pipe) {
        // do something when exception caught

        return $data;
    })->then(function ($data) {
        return $data;
    });
```

You can as well instantiate the pipeline using IoC or manually:
```php
app(Pipeline::class)
    ...

(new Pipeline(app()))
    ...
```

## Testing
```bash
composer test
```

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
