<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingAmqplibRabbitMqBundle;

use Auxmoney\OpentracingAmqplibRabbitMqBundle\DependencyInjection\AmqplibRabbitMqConsumerCompilerPass;
use Auxmoney\OpentracingAmqplibRabbitMqBundle\DependencyInjection\AmqplibRabbitMqProducerCompilerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OpenTracingAmqplibRabbitMqBundleTest extends TestCase
{
    /** @var OpentracingAmqplibRabbitMqBundle */
    private $subject;

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
        )->shouldBeCalled();

        $containerBuilder->addCompilerPass(
            new AmqplibRabbitMqConsumerCompilerPass(),
            'beforeOptimization',
            -999
        )->shouldBeCalled();

        $this->subject->build($containerBuilder->reveal());
    }
}
