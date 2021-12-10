<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingAmqplibRabbitMqBundle\Tests\Unit\DependencyInjection;

use Auxmoney\OpentracingAmqplibRabbitMqBundle\DependencyInjection\AmqplibRabbitMqProducerCompilerPass;
use Auxmoney\OpentracingAmqplibRabbitMqBundle\Producer\Producer;
use Auxmoney\OpentracingBundle\Service\Tracing;
use Auxmoney\OpentracingBundle\Service\TracingService;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class AmqplibRabbitMqProducerCompilerPassTest extends TestCase
{
    use ProphecyTrait;

    /** @var AmqplibRabbitMqProducerCompilerPass */
    private $subject;

    public function setUp(): void
    {
        $this->subject = new AmqplibRabbitMqProducerCompilerPass();
    }

    public function testProcessWithConsumerClassSubstitution(): void
    {
        $nonProducerDefinition = new Definition(stdClass::class);
        $nonProducerDefinition->setTags(['someTag' => [], 'someOtherTag' => []]);

        $producerDefinition = new Definition(stdClass::class);
        $producerDefinition->setTags(['someTag' => [], 'old_sound_rabbit_mq.producer' => []]);
        $producerDefinition->setArguments(['arg1', 'arg2']);

        $containerBuilder = $this->prophesize(ContainerBuilder::class);
        $containerBuilder
            ->getDefinitions()
            ->shouldBeCalledTimes(1)
            ->willReturn([$nonProducerDefinition, $producerDefinition]);

        $tracingDefinition = new Definition(TracingService::class);

        $containerBuilder
            ->getDefinition(Tracing::class)
            ->shouldBeCalledTimes(1)
            ->willReturn($tracingDefinition);

        $this->subject->process($containerBuilder->reveal());

        self::assertEquals(stdClass::class, $nonProducerDefinition->getClass());
        self::assertEquals(Producer::class, $producerDefinition->getClass());
        self::assertEquals($tracingDefinition, $producerDefinition->getArguments()[0]);
    }
}
