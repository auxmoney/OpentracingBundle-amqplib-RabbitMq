<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingAmqplibRabbitMqBundle\Tests\Functional;

use Auxmoney\OpentracingBundle\Tests\Functional\JaegerConsoleFunctionalTest;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class FunctionalTest extends JaegerConsoleFunctionalTest
{
    public function testTraceIncludesMessageConsumer(): void
    {
        $this->setUpTestProject('default');

        $this->runInTestProject(['symfony', 'console', 'rabbitmq:setup-fabric']);

        $process = new Process(['symfony', 'console', 'test:message'], 'build/testproject');
        $process->mustRun();
        $output = $process->getOutput();
        $traceId = substr($output, 0, strpos($output, ':'));
        self::assertNotEmpty($traceId);

        $this->runInTestProject(['symfony', 'console', 'rabbitmq:consumer', '-m', '1', 'test_queue']);

        $spans = $this->getSpansFromTrace($this->getTraceFromJaegerAPI($traceId));
        self::assertCount(4, $spans);

        $traceAsYAML = $this->getSpansAsYAML($spans, '[].{operationName: operationName, startTime: startTime, spanID: spanID, references: references, tags: tags[?key==\'command.exit-code\' || key==\'test.tag\'].{key: key, value: value}}');
        self::assertStringEqualsFile(__DIR__ . '/FunctionalTest.expected.yaml', $traceAsYAML);
    }
}
