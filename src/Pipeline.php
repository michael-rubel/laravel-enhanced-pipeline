<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedPipeline;

use Closure;
use Illuminate\Container\Container as ContainerConcrete;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Pipeline\Pipeline as PipelineContract;
use Illuminate\Support\Traits\Conditionable;
use MichaelRubel\EnhancedPipeline\Events\PipeExecutionFinished;
use MichaelRubel\EnhancedPipeline\Events\PipeExecutionStarted;
use MichaelRubel\EnhancedPipeline\Events\PipelineFinished;
use MichaelRubel\EnhancedPipeline\Events\PipelineStarted;
use MichaelRubel\EnhancedPipeline\Traits\HasDatabaseTransactions;
use MichaelRubel\EnhancedPipeline\Traits\HasEvents;
use RuntimeException;
use Throwable;

class Pipeline implements PipelineContract
{
    use Conditionable, HasDatabaseTransactions, HasEvents;

    /**
     * The container implementation.
     */
    protected ?Container $container;

    /**
     * The object being passed through the pipeline.
     */
    protected mixed $passable;

    /**
     * The callback to be executed on failure pipeline.
     */
    protected ?Closure $onFailure = null;

    /**
     * The array of class pipes.
     */
    protected array $pipes = [];

    /**
     * The method to call on each pipe.
     */
    protected string $method = 'handle';

    /**
     * Create a new class instance.
     *
     * @return void
     */
    public function __construct(?Container $container = null)
    {
        $this->container = $container;
    }

    /**
     * Create a new class instance.
     */
    public static function make(?Container $container = null): Pipeline
    {
        if (! $container) {
            $container = ContainerConcrete::getInstance();
        }

        return $container->make(static::class);
    }

    /**
     * Set the object being sent through the pipeline.
     */
    public function send(mixed $passable): static
    {
        $this->passable = $passable;

        return $this;
    }

    /**
     * Set the array of pipes.
     */
    public function through(mixed $pipes): static
    {
        $this->pipes = is_array($pipes) ? $pipes : func_get_args();

        return $this;
    }

    /**
     * Push additional pipes onto the pipeline.
     */
    public function pipe(mixed $pipes): static
    {
        array_push($this->pipes, ...(is_array($pipes) ? $pipes : func_get_args()));

        return $this;
    }

    /**
     * Set the method to call on the pipes.
     *
     * @param  string  $method
     */
    public function via($method): static
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Run the pipeline with a final destination callback.
     */
    public function then(Closure $destination): mixed
    {
        try {
            $this->fireEvent(PipelineStarted::class,
                $destination,
                $this->passable,
                $this->pipes(),
                $this->useTransaction,
            );

            $this->beginTransaction();

            $pipeline = array_reduce(
                array_reverse($this->pipes()),
                $this->carry(),
                $this->prepareDestination($destination)
            );

            $result = $pipeline($this->passable);

            $this->commitTransaction();

            $this->fireEvent(PipelineFinished::class,
                $destination,
                $this->passable,
                $this->pipes(),
                $this->useTransaction,
                $result,
            );

            return $result;
        } catch (Throwable $e) {
            $this->rollbackTransaction();

            if ($this->onFailure) {
                return ($this->onFailure)($this->passable, $e);
            }

            return $this->handleException($e);
        }
    }

    /**
     * Run the pipeline and return the result.
     */
    public function thenReturn(): mixed
    {
        return $this->then(function ($passable) {
            return $passable;
        });
    }

    /**
     * Get the final piece of the Closure onion.
     */
    protected function prepareDestination(Closure $destination): Closure
    {
        return function ($passable) use ($destination) {
            return $destination($passable);
        };
    }

    /**
     * Get a Closure that represents a slice of the application onion.
     */
    protected function carry(): Closure
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                $this->fireEvent(PipeExecutionStarted::class, $pipe, $passable);

                if (is_callable($pipe)) {
                    // If the pipe is a callable, then we will call it directly, but otherwise we
                    // will resolve the pipes out of the dependency container and call it with
                    // the appropriate method and arguments, returning the results back out.
                    $result = $pipe($passable, $stack);

                    $this->fireEvent(PipeExecutionFinished::class, $pipe, $passable);

                    return $result;
                } elseif (! is_object($pipe)) {
                    [$name, $parameters] = $this->parsePipeString($pipe);

                    // If the pipe is a string we will parse the string and resolve the class out
                    // of the dependency injection container. We can then build a callable and
                    // execute the pipe function giving in the parameters that are required.
                    $pipe = $this->getContainer()->make($name);

                    $parameters = array_merge([$passable, $stack], $parameters);
                } else {
                    // If the pipe is already an object we'll just make a callable and pass it to
                    // the pipe as-is. There is no need to do any extra parsing and formatting
                    // since the object we're given was already a fully instantiated object.
                    $parameters = [$passable, $stack];
                }

                $carry = method_exists($pipe, $this->method)
                                ? $pipe->{$this->method}(...$parameters)
                                : $pipe(...$parameters);

                $this->fireEvent(PipeExecutionFinished::class, $pipe, $passable);

                return $this->handleCarry($carry);
            };
        };
    }

    /**
     * Parse full pipe string to get name and parameters.
     */
    protected function parsePipeString(string $pipe): array
    {
        [$name, $parameters] = array_pad(explode(':', $pipe, 2), 2, []);

        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }

        return [$name, $parameters];
    }

    /**
     * Get the array of configured pipes.
     */
    protected function pipes(): array
    {
        return $this->pipes;
    }

    /**
     * Get the container instance.
     */
    protected function getContainer(): ?Container
    {
        if (! $this->container) {
            throw new RuntimeException('A container instance has not been passed to the Pipeline.');
        }

        return $this->container;
    }

    /**
     * Set the container instance.
     */
    public function setContainer(Container $container): static
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Set callback to be executed on failure pipeline.
     */
    public function onFailure(Closure $callback): static
    {
        $this->onFailure = $callback;

        return $this;
    }

    /**
     * Run a single pipe.
     */
    public function run(string $pipe, mixed $data = true): mixed
    {
        return $this
            ->send($data)
            ->through([$pipe])
            ->thenReturn();
    }

    /**
     * Handle the value returned from each pipe before passing it to the next.
     */
    protected function handleCarry(mixed $carry): mixed
    {
        return $carry;
    }

    /**
     * Handle the given exception.
     *
     * @throws Throwable
     */
    protected function handleException(Throwable $e)
    {
        throw $e;
    }
}
