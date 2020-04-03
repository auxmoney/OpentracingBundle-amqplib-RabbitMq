<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingAmqplibRabbitMqBundle\Tests\Unit\DependencyInjection;

use Auxmoney\OpentracingAmqplibRabbitMqBundle\DependencyInjection\OpentracingAmqplibRabbitMqExtension;
use Auxmoney\OpentracingAmqplibRabbitMqBundle\EventSubscriber\AfterMessageProcessingSubscriber;
use Auxmoney\OpentracingAmqplibRabbitMqBundle\EventSubscriber\BeforeMessageProcessingSubscriber;
use Auxmoney\OpentracingAmqplibRabbitMqBundle\EventSubscriber\FinishCommandSpanSubscriberDecorator;
use Auxmoney\OpentracingAmqplibRabbitMqBundle\EventSubscriber\StartCommandSpanSubscriberDecorator;
use Auxmoney\OpentracingBundle\EventListener\FinishCommandSpanSubscriber;
use Auxmoney\OpentracingBundle\EventListener\StartCommandSpanSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OpentracingAmqplibRabbitMqExtensionTest extends TestCase
{
    /** @var OpentracingAmqplibRabbitMqExtension */
    private $subject;

    public function setUp(): void
    {
        $this->subject = new OpentracingAmqplibRabbitMqExtension();
    }

    public function testLoad(): void
    {
        $container = new ContainerBuilder();

        $this->subject->load([], $container);

        self::assertTrue($container->hasDefinition(AfterMessageProcessingSubscriber::class));
        self::assertTrue($container->hasDefinition(BeforeMessageProcessingSubscriber::class));

        self::assertTrue($container->hasDefinition(StartCommandSpanSubscriberDecorator::class));
        $startCommandSpanDefinition = $container->getDefinition(StartCommandSpanSubscriberDecorator::class);
        $expectedDefinitionInnerId = sprintf('%s.inner', StartCommandSpanSubscriberDecorator::class);
        self::assertEquals($expectedDefinitionInnerId, $startCommandSpanDefinition->getArgument(0)->__toString());
        self::assertEquals(StartCommandSpanSubscriber::class, $startCommandSpanDefinition->getDecoratedService()[0]);

        self::assertTrue($container->hasDefinition(FinishCommandSpanSubscriberDecorator::class));
        $finishCommandSpanDefinition = $container->getDefinition(FinishCommandSpanSubscriberDecorator::class);
        $expectedDefinitionInnerId = sprintf('%s.inner', FinishCommandSpanSubscriberDecorator::class);
        self::assertEquals($expectedDefinitionInnerId, $finishCommandSpanDefinition->getArgument(0)->__toString());
        self::assertEquals(FinishCommandSpanSubscriber::class, $finishCommandSpanDefinition->getDecoratedService()[0]);
    }
}
