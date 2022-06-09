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
use MichaelRubel\EnhancedPipeline\Pipeline;
```

Then you can make the pipeline:
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
        // do something when all pipes completed their work

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

If you want to override the original Laravel's pipeline resolved through IoC Container, you can add binding in the ServiceProvider's register method:
```php
$this->app->singleton(\Illuminate\Pipeline\Pipeline::class, \MichaelRubel\EnhancedPipeline\Pipeline::class);
```

## Credits
- [chefhasteeth](https://github.com/chefhasteeth) for implementation of DB transaction in Pipeline.
- [rezaamini-ir](https://github.com/rezaamini-ir) for inspiration to create pipeline with `onFailure` method. See [#PR](https://github.com/laravel/framework/pull/42634)

## Testing
```bash
composer test
```

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
