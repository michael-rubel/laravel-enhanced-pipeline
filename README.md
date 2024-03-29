![Enhanced Pipeline in Laravel](https://user-images.githubusercontent.com/37669560/183900755-de9856b2-012e-4a56-a99f-dd46d70538be.png)

# Laravel Enhanced Pipeline
[![Latest Version on Packagist](https://img.shields.io/packagist/v/michael-rubel/laravel-enhanced-pipeline.svg?style=flat-square&logo=packagist)](https://packagist.org/packages/michael-rubel/laravel-enhanced-pipeline)
[![Total Downloads](https://img.shields.io/packagist/dt/michael-rubel/laravel-enhanced-pipeline.svg?style=flat-square&logo=packagist)](https://packagist.org/packages/michael-rubel/laravel-enhanced-pipeline)
[![Code Quality](https://img.shields.io/scrutinizer/quality/g/michael-rubel/laravel-enhanced-pipeline.svg?style=flat-square&logo=scrutinizer)](https://scrutinizer-ci.com/g/michael-rubel/laravel-enhanced-pipeline/?branch=main)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/michael-rubel/laravel-enhanced-pipeline.svg?style=flat-square&logo=scrutinizer)](https://scrutinizer-ci.com/g/michael-rubel/laravel-enhanced-pipeline/?branch=main)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/michael-rubel/laravel-enhanced-pipeline/run-tests.yml?branch=main&style=flat-square&label=tests&logo=github)](https://github.com/michael-rubel/laravel-enhanced-pipeline/actions)
[![PHPStan](https://img.shields.io/github/actions/workflow/status/michael-rubel/laravel-enhanced-pipeline/phpstan.yml?branch=main&style=flat-square&label=larastan&logo=laravel)](https://github.com/michael-rubel/laravel-enhanced-pipeline/actions)

Laravel Pipeline with DB transaction support, events and additional methods.

The package requires `PHP 8.1` or higher and `Laravel 10` or higher.

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

Then use the pipeline:
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

You can as well instantiate the pipeline using the service container or manually:
```php
app(Pipeline::class)
    ...

(new Pipeline(app()))
    ...

(new Pipeline)
    ->setContainer(app())
    ...
```

You can use the `run` method to execute a single pipe:
```php
$pipeline = Pipeline::make();

$pipeline->run(Pipe::class, $data);
```

By default, `run` uses the `handle` method in your class as an entry point, but if you use a different method name in your pipelines, you can fix that by adding code to your ServiceProvider:
```php
$this->app->resolving(Pipeline::class, function ($pipeline) {
    return $pipeline->via('execute');
});
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
- [`PipelineStarted`](https://github.com/michael-rubel/laravel-enhanced-pipeline/blob/main/src/Events/PipelineStarted.php) - fired when the pipeline starts working;
- [`PipelineFinished`](https://github.com/michael-rubel/laravel-enhanced-pipeline/blob/main/src/Events/PipelineFinished.php) - fired when the pipeline finishes its work;
- [`PipeExecutionStarted`](https://github.com/michael-rubel/laravel-enhanced-pipeline/blob/main/src/Events/PipeExecutionStarted.php) - fired **before** execution of the pipe;
- [`PipeExecutionFinished`](https://github.com/michael-rubel/laravel-enhanced-pipeline/blob/main/src/Events/PipeExecutionFinished.php) - fired **after** execution of the pipe.

## Testing
```bash
composer test
```

## Credits
- [chefhasteeth](https://github.com/chefhasteeth) for base implementation of DB transaction in Pipeline.
- [rezaamini-ir](https://github.com/rezaamini-ir) for inspiration to create a pipeline with `onFailure` method. See [#PR](https://github.com/laravel/framework/pull/42634)

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
