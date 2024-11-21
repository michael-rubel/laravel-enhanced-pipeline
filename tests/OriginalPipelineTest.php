<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedPipeline\Tests;

use Illuminate\Container\Container;
use MichaelRubel\EnhancedPipeline\Pipeline;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

class OriginalPipelineTest extends TestCase
{
    public function test_pipeline_basic_usage()
    {
        $pipeTwo = function ($piped, $next) {
            $_SERVER['__test.pipe.two'] = $piped;

            return $next($piped);
        };

        $result = (new Pipeline(new Container))
            ->send('foo')
            ->through([PipelineTestPipeOne::class, $pipeTwo])
            ->then(function ($piped) {
                return $piped;
            });

        $this->assertSame('foo', $result);
        $this->assertSame('foo', $_SERVER['__test.pipe.one']);
        $this->assertSame('foo', $_SERVER['__test.pipe.two']);

        unset($_SERVER['__test.pipe.one'], $_SERVER['__test.pipe.two']);
    }

    public function test_pipeline_usage_with_objects()
    {
        $result = (new Pipeline(new Container))
            ->send('foo')
            ->through([new PipelineTestPipeOne])
            ->then(function ($piped) {
                return $piped;
            });

        $this->assertSame('foo', $result);
        $this->assertSame('foo', $_SERVER['__test.pipe.one']);

        unset($_SERVER['__test.pipe.one']);
    }

    public function test_pipeline_usage_with_invokable_objects()
    {
        $result = (new Pipeline(new Container))
            ->send('foo')
            ->through([new PipelineTestPipeTwo])
            ->then(
                function ($piped) {
                    return $piped;
                }
            );

        $this->assertSame('foo', $result);
        $this->assertSame('foo', $_SERVER['__test.pipe.one']);

        unset($_SERVER['__test.pipe.one']);
    }

    public function test_pipeline_usage_with_callable()
    {
        $function = function ($piped, $next) {
            $_SERVER['__test.pipe.one'] = 'foo';

            return $next($piped);
        };

        $result = (new Pipeline(new Container))
            ->send('foo')
            ->through([$function])
            ->then(
                function ($piped) {
                    return $piped;
                }
            );

        $this->assertSame('foo', $result);
        $this->assertSame('foo', $_SERVER['__test.pipe.one']);

        unset($_SERVER['__test.pipe.one']);

        $result = (new Pipeline(new Container))
            ->send('bar')
            ->through($function)
            ->thenReturn();

        $this->assertSame('bar', $result);
        $this->assertSame('foo', $_SERVER['__test.pipe.one']);

        unset($_SERVER['__test.pipe.one']);
    }

    public function test_pipeline_usage_with_pipe()
    {
        $object = new stdClass;

        $object->value = 0;

        $function = function ($object, $next) {
            $object->value++;

            return $next($object);
        };

        $result = (new Pipeline(new Container))
            ->send($object)
            ->through([$function])
            ->pipe([$function])
            ->then(
                function ($piped) {
                    return $piped;
                }
            );

        $this->assertSame($object, $result);
        $this->assertEquals(2, $object->value);
    }

    public function test_pipeline_usage_with_invokable_class()
    {
        $result = (new Pipeline(new Container))
            ->send('foo')
            ->through([PipelineTestPipeTwo::class])
            ->then(
                function ($piped) {
                    return $piped;
                }
            );

        $this->assertSame('foo', $result);
        $this->assertSame('foo', $_SERVER['__test.pipe.one']);

        unset($_SERVER['__test.pipe.one']);
    }

    public function test_then_method_is_not_called_if_the_pipe_returns()
    {
        $_SERVER['__test.pipe.then'] = '(*_*)';
        $_SERVER['__test.pipe.second'] = '(*_*)';

        $result = (new Pipeline(new Container))
            ->send('foo')
            ->through([
                fn ($value, $next) => 'm(-_-)m',
                fn ($value, $next) => $_SERVER['__test.pipe.second'] = 'm(-_-)m',
            ])
            ->then(function ($piped) {
                $_SERVER['__test.pipe.then'] = '(0_0)';

                return $piped;
            });

        $this->assertSame('m(-_-)m', $result);
        // The then callback is not called.
        $this->assertSame('(*_*)', $_SERVER['__test.pipe.then']);
        // The second pipe is not called.
        $this->assertSame('(*_*)', $_SERVER['__test.pipe.second']);

        unset($_SERVER['__test.pipe.then']);
    }

    public function test_then_method_input_value()
    {
        $result = (new Pipeline(new Container))
            ->send('foo')
            ->through([function ($value, $next) {
                $value = $next('::not_foo::');

                $_SERVER['__test.pipe.return'] = $value;

                return 'pipe::'.$value;
            }])
            ->then(function ($piped) {
                $_SERVER['__test.then.arg'] = $piped;

                return 'then'.$piped;
            });

        $this->assertSame('pipe::then::not_foo::', $result);
        $this->assertSame('::not_foo::', $_SERVER['__test.then.arg']);

        unset($_SERVER['__test.then.arg']);
        unset($_SERVER['__test.pipe.return']);
    }

    public function test_pipeline_usage_with_parameters()
    {
        $parameters = ['one', 'two'];

        $result = (new Pipeline(new Container))
            ->send('foo')
            ->through(PipelineTestParameterPipe::class.':'.implode(',', $parameters))
            ->then(function ($piped) {
                return $piped;
            });

        $this->assertSame('foo', $result);
        $this->assertEquals($parameters, $_SERVER['__test.pipe.parameters']);

        unset($_SERVER['__test.pipe.parameters']);
    }

    public function test_pipeline_via_changes_the_method_being_called_on_the_pipes()
    {
        $pipelineInstance = new Pipeline(new Container);
        $result = $pipelineInstance->send('data')
            ->through(PipelineTestPipeOne::class)
            ->via('differentMethod')
            ->then(function ($piped) {
                return $piped;
            });
        $this->assertSame('data', $result);
    }

    public function test_pipeline_throws_exception_on_resolve_without_container()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('A container instance has not been passed to the Pipeline.');

        (new Pipeline)->send('data')
            ->through(PipelineTestPipeOne::class)
            ->then(function ($piped) {
                return $piped;
            });
    }

    public function test_pipeline_then_return_method_runs_pipeline_then_returns_passable()
    {
        $result = (new Pipeline(new Container))
            ->send('foo')
            ->through([PipelineTestPipeOne::class])
            ->thenReturn();

        $this->assertSame('foo', $result);
        $this->assertSame('foo', $_SERVER['__test.pipe.one']);

        unset($_SERVER['__test.pipe.one']);
    }

    public function test_pipeline_conditionable()
    {
        $result = (new Pipeline(new Container))
            ->send('foo')
            ->when(true, function (Pipeline $pipeline) {
                $pipeline->pipe([PipelineTestPipeOne::class]);
            })
            ->then(function ($piped) {
                return $piped;
            });

        $this->assertSame('foo', $result);
        $this->assertSame('foo', $_SERVER['__test.pipe.one']);
        unset($_SERVER['__test.pipe.one']);

        $_SERVER['__test.pipe.one'] = null;
        $result = (new Pipeline(new Container))
            ->send('foo')
            ->when(false, function (Pipeline $pipeline) {
                $pipeline->pipe([PipelineTestPipeOne::class]);
            })
            ->then(function ($piped) {
                return $piped;
            });

        $this->assertSame('foo', $result);
        $this->assertNull($_SERVER['__test.pipe.one']);
        unset($_SERVER['__test.pipe.one']);
    }
}

class PipelineTestPipeOne
{
    public function handle($piped, $next)
    {
        $_SERVER['__test.pipe.one'] = $piped;

        return $next($piped);
    }

    public function differentMethod($piped, $next)
    {
        return $next($piped);
    }
}

class PipelineTestPipeTwo
{
    public function __invoke($piped, $next)
    {
        $_SERVER['__test.pipe.one'] = $piped;

        return $next($piped);
    }
}

class PipelineTestParameterPipe
{
    public function handle($piped, $next, $parameter1 = null, $parameter2 = null)
    {
        $_SERVER['__test.pipe.parameters'] = [$parameter1, $parameter2];

        return $next($piped);
    }
}
