![Enhanced Pipeline in Laravel](https://user-images.githubusercontent.com/37669560/183900755-de9856b2-012e-4a56-a99f-dd46d70538be.png)

# Laravel Enhanced Pipeline
[![Latest Version on Packagist](https://img.shields.io/packagist/v/michael-rubel/laravel-enhanced-pipeline.svg?style=flat-square&logo=packagist)](https://packagist.org/packages/michael-rubel/laravel-enhanced-pipeline)
[![Total Downloads](https://img.shields.io/packagist/dt/michael-rubel/laravel-enhanced-pipeline.svg?style=flat-square&logo=packagist)](https://packagist.org/packages/michael-rubel/laravel-enhanced-pipeline)
[![Code Quality](https://img.shields.io/scrutinizer/quality/g/michael-rubel/laravel-enhanced-pipeline.svg?style=flat-square&logo=scrutinizer)](https://scrutinizer-ci.com/g/michael-rubel/laravel-enhanced-pipeline/?branch=main)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/michael-rubel/laravel-enhanced-pipeline.svg?style=flat-square&logo=scrutinizer)](https://scrutinizer-ci.com/g/michael-rubel/laravel-enhanced-pipeline/?branch=main)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/michael-rubel/laravel-enhanced-pipeline/run-tests/main?style=flat-square&label=tests&logo=github)](https://github.com/michael-rubel/laravel-enhanced-pipeline/actions)
[![PHPStan](https://img.shields.io/github/workflow/status/michael-rubel/laravel-enhanced-pipeline/phpstan/main?style=flat-square&label=larastan&logo=laravel)](https://github.com/michael-rubel/laravel-enhanced-pipeline/actions)

Laravel pipelines with DB transaction support and handy additional methods.

---

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
    ->withEvents()
    ->withTransaction()
    ->send($data)
    ->through([
        // your pipes
    ])
    ->onFailure(function ($data, $exception) {
        // do something when exception caught

        return $data;
    })->then(function ($data) {
        // do something when all pipes completed their work

        return $data;
    });
```

You can use the `pipeline` helper:
```php
pipeline($data, [
    // pipes
])->thenReturn();
```

You can as well instantiate the pipeline using IoC or manually:
```php
app(Pipeline::class)
    ...

(new Pipeline(app()))
    ...

(new Pipeline)
    ->setContainer(app())
    ...
```

If you want to override the original [Pipeline](https://github.com/laravel/framework/blob/9.x/src/Illuminate/Pipeline/Pipeline.php) resolved through IoC Container, you can add binding in the ServiceProvider `register` method:
```php
$this->app->singleton(\Illuminate\Pipeline\Pipeline::class, \MichaelRubel\EnhancedPipeline\Pipeline::class);
```

## Transaction
Usage of `withTransaction` method will enable a [`manual DB transaction`](https://laravel.com/docs/9.x/database#manually-using-transactions) throughout the pipeline execution.

## Events
Usage of `withEvents` method will enable [`Laravel Events`](https://laravel.com/docs/9.x/events#introduction) throughout the pipeline execution.

#### Available events
- [`PipeStarted`](https://github.com/michael-rubel/laravel-enhanced-pipeline/blob/main/src/Events/PipeStarted.php) - fired **before** execution of pipe;
- [`PipePassed`](https://github.com/michael-rubel/laravel-enhanced-pipeline/blob/main/src/Events/PipePassed.php) - fired **after** execution of pipe.

## Testing
```bash
composer test
```

## Credits
- [chefhasteeth](https://github.com/chefhasteeth) for base implementation of DB transaction in Pipeline.
- [rezaamini-ir](https://github.com/rezaamini-ir) for inspiration to create a pipeline with `onFailure` method. See [#PR](https://github.com/laravel/framework/pull/42634)

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
