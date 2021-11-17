<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingAmqplibRabbitMqBundle\Tests\Unit\EventSubscriber;

use Auxmoney\OpentracingAmqplibRabbitMqBundle\Consumer\Consumer;
use Auxmoney\OpentracingAmqplibRabbitMqBundle\EventSubscriber\BeforeMessageProcessingSubscriber;
use Auxmoney\OpentracingBundle\Internal\Utility;
use Auxmoney\OpentracingBundle\Service\Tracing;
use OldSound\RabbitMqBundle\Event\AMQPEvent;
use OpenTracing\Reference;
use OpenTracing\SpanContext;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class BeforeMessageProcessingSubscriberTest extends TestCase
{
    /** @var Tracing|ObjectProphecy */
    private $tracing;
    /** @var Utility|ObjectProphecy */
    private $utility;
    /** @var BeforeMessageProcessingSubscriber */
    private $subject;

    public function setUp(): void
    {
        $this->tracing = $this->prophesize(Tracing::class);
        $this->utility = $this->prophesize(Utility::class);

        $this->subject = new BeforeMessageProcessingSubscriber($this->tracing->reveal(), $this->utility->reveal());
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertArrayHasKey('before_processing', BeforeMessageProcessingSubscriber::getSubscribedEvents());
    }

    public function testOnBeforeMessageProcessingWithTraceableApplicationHeaders(): void
    {
        $headerTable = new AMQPTable(['foo' => 'bar', 'text_map' => '123456789']);

        $amqpMessage = $this->prophesize(AMQPMessage::class);
        $amqpMessage
            ->get_properties()
            ->shouldBeCalledTimes(1)
            ->willReturn(['application_headers' => $headerTable]);

        $connection = $this->prophesize(AbstractConnection::class);

        $consumer = new Consumer($connection->reveal());
        $consumer->setQueueOptions(['name' => 'QueueName']);

        $amqpEvent = $this->prophesize(AMQPEvent::class);
        $amqpEvent->getAMQPMessage()->shouldBeCalledTimes(1)->willReturn($amqpMessage->reveal());
        $amqpEvent->getConsumer()->shouldBeCalledTimes(1)->willReturn($consumer);

        $spanContext = $this->prophesize(SpanContext::class);

        $this->utility
            ->extractSpanContext(['foo' => 'bar', 'text_map' => '123456789'])
            ->shouldBeCalledTimes(1)
            ->willReturn($spanContext->reveal());

        $expectedSpanOptions = [
            'tags' => [
                'span.kind' => 'consumer',
                'message_bus.queue_name' => 'QueueName',
                'auxmoney-opentracing-bundle.span-origin' => 'rabbitmq:consumer',
            ],
            'references' => new Reference('follows_from', $spanContext->reveal())
        ];

        $this->tracing
            ->startActiveSpan(
                'RabbitMq: Processing message from "QueueName" queue',
                $expectedSpanOptions
            )->shouldBeCalledTimes(1);

        $this->subject->onBeforeMessageProcessing($amqpEvent->reveal());
    }

    public function testOnBeforeMessageProcessingWithUntraceableApplicationHeaders(): void
    {
        $headerTable = new AMQPTable(['foo' => 'bar']);

        $amqpMessage = $this->prophesize(AMQPMessage::class);
        $amqpMessage
            ->get_properties()
            ->shouldBeCalledTimes(1)
            ->willReturn(['application_headers' => $headerTable]);

        $connection = $this->prophesize(AbstractConnection::class);

        $consumer = new Consumer($connection->reveal());
        $consumer->setQueueOptions(['name' => 'QueueName']);

        $amqpEvent = $this->prophesize(AMQPEvent::class);
        $amqpEvent->getAMQPMessage()->shouldBeCalledTimes(1)->willReturn($amqpMessage->reveal());
        $amqpEvent->getConsumer()->shouldBeCalledTimes(1)->willReturn($consumer);

        $this->utility
            ->extractSpanContext(['foo' => 'bar'])
            ->shouldBeCalledTimes(1)
            ->willReturn(null);

        $expectedSpanOptions = [
            'tags' => [
                'span.kind' => 'consumer',
                'message_bus.queue_name' => 'QueueName',
                'auxmoney-opentracing-bundle.span-origin' => 'rabbitmq:consumer',
            ]
        ];

        $this->tracing
            ->startActiveSpan(
                'RabbitMq: Processing message from "QueueName" queue',
                $expectedSpanOptions
            )->shouldBeCalledTimes(1);

        $this->subject->onBeforeMessageProcessing($amqpEvent->reveal());
    }

    public function testOnBeforeMessageProcessingWithoutApplicationHeaders(): void
    {
        $headerTable = new AMQPTable([]);

        $amqpMessage = $this->prophesize(AMQPMessage::class);
        $amqpMessage
            ->get_properties()
            ->shouldBeCalledTimes(1)
            ->willReturn(['X-DEATH' => $headerTable]);

        $connection = $this->prophesize(AbstractConnection::class);

        $consumer = new Consumer($connection->reveal());
        $consumer->setQueueOptions(['name' => 'QueueName']);

        $amqpEvent = $this->prophesize(AMQPEvent::class);
        $amqpEvent->getAMQPMessage()->shouldBeCalledTimes(1)->willReturn($amqpMessage->reveal());
        $amqpEvent->getConsumer()->shouldBeCalledTimes(1)->willReturn($consumer);

        $this->utility
            ->extractSpanContext(Argument::any())
            ->shouldNotBeCalled();

        $expectedSpanOptions = [
            'tags' => [
                'span.kind' => 'consumer',
                'message_bus.queue_name' => 'QueueName',
                'auxmoney-opentracing-bundle.span-origin' => 'rabbitmq:consumer',
            ]
        ];

        $this->tracing
            ->startActiveSpan(
                'RabbitMq: Processing message from "QueueName" queue',
                $expectedSpanOptions
            )->shouldBeCalledTimes(1);

        $this->subject->onBeforeMessageProcessing($amqpEvent->reveal());
    }
}
