<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingAmqplibRabbitMqBundle\Tests\Unit;

use Auxmoney\OpentracingAmqplibRabbitMqBundle\DependencyInjection\AmqplibRabbitMqConsumerCompilerPass;
use Auxmoney\OpentracingAmqplibRabbitMqBundle\DependencyInjection\AmqplibRabbitMqProducerCompilerPass;
use Auxmoney\OpentracingAmqplibRabbitMqBundle\OpentracingAmqplibRabbitMqBundle;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OpenTracingAmqplibRabbitMqBundleTest extends TestCase
{
    use ProphecyTrait;

    private OpentracingAmqplibRabbitMqBundle $subject;

    public function setUp(): void
    {
        $this->subject = new OpentracingAmqplibRabbitMqBundle();
    }

    public function testBuild(): void
    {
        $containerBuilder = $this->prophesize(ContainerBuilder::class);

        $containerBuilder->addCompilerPass(
            new AmqplibRabbitMqProducerCompilerPass(),
            'beforeOptimization',
            -999
        )->shouldBeCalled()->willReturn($containerBuilder->reveal());

        $containerBuilder->addCompilerPass(
            new AmqplibRabbitMqConsumerCompilerPass(),
            'beforeOptimization',
            -999
        )->shouldBeCalled()->willReturn($containerBuilder->reveal());

        $this->subject->build($containerBuilder->reveal());
    }
}
