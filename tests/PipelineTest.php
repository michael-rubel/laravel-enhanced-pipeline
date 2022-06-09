<?php

namespace MichaelRubel\EnhancedPipeline\Tests;

class PipelineTest extends TestCase
{
    public function testExceptionIsHandledByOnFailureMethodInPipelineWithBoundClass()
    {
        $result = app(\Illuminate\Pipeline\Pipeline::class)
            ->send('data')
            ->through(PipelineWithException::class)
            ->onFailure(function () {
                return 'error';
            })->then(function ($piped) {
                return $piped;
            });

        $this->assertEquals('error', $result);
    }

    public function testExceptionIsHandledByOnFailureMethodInPipelineWithOriginalClass()
    {
        $result = app(\MichaelRubel\EnhancedPipeline\Pipeline::class)
            ->send('data')
            ->through(PipelineWithException::class)
            ->onFailure(function () {
                return 'error';
            })->then(function ($piped) {
                return $piped;
            });

        $this->assertEquals('error', $result);
    }
}

class PipelineWithException
{
    public function handle($piped, $next)
    {
        throw new \Exception('Foo');
    }
}
