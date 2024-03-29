<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingAmqplibRabbitMqBundle\Tests\Unit\Producer;

use Auxmoney\OpentracingAmqplibRabbitMqBundle\Producer\Producer;
use Auxmoney\OpentracingBundle\Service\Tracing;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class ProducerTest extends TestCase
{
    use ProphecyTrait;

    /** @var Tracing|ObjectProphecy */
    private $tracingService;
    /** @var LoggerInterface|ObjectProphecy */
    private $logger;
    /** @var Producer */
    private Producer $subject;

    public function setUp(): void
    {
        $this->tracingService = $this->prophesize(Tracing::class);
        $connection = $this->prophesize(AbstractConnection::class);
        $connection->connectOnConstruct()->willReturn(false);
        $channel = $this->prophesize(AMQPChannel::class);
        $channel->getChannelId()->willReturn('channelId');
        $channel->basic_publish(Argument::cetera())->shouldBeCalledTimes(1);

        $this->subject = new Producer(
            $this->tracingService->reveal(),
            $connection->reveal(),
            $channel->reveal()
        );

        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->subject->setLogger($this->logger->reveal());
        $this->subject->disableAutoSetupFabric();

        $this->subject->setExchangeOptions(['name' => 'exchangeName', 'type' => 'direct']);
    }

    public function testPublishWithoutHeaders(): void
    {
        $this->tracingService->startActiveSpan(
            'RabbitMq: Publishing message to "exchangeName" exchange',
            [
                'tags' => [
                    'message_bus.routing_key' => 'none',
                    'message_bus.exchange_name' => 'exchangeName',
                    'span.kind' => 'producer',
                    'auxmoney-opentracing-bundle.span-origin' => 'rabbitmq:producer',
                ]
            ]
        )->shouldBeCalledTimes(1);

        $this->tracingService
            ->injectTracingHeadersIntoCarrier([])
            ->shouldBeCalledTimes(1)
            ->willReturn(['UBER-TRACING-ID' => '123456789']);

        $this->tracingService->finishActiveSpan()->shouldBeCalledTimes(1);

        $this->logger->debug(
            Argument::any(),
            [
                'amqp' => [
                    'body' => 'msgBody',
                    'routingkeys' => '',
                    'properties' => [],
                    'headers' => ['UBER-TRACING-ID' => '123456789']
                ]
            ]
        )->shouldBeCalledTimes(1);

        $this->subject->publish('msgBody');
    }

    public function testPublishWithHeaders(): void
    {
        $this->tracingService->startActiveSpan(
            'RabbitMq: Publishing message to "exchangeName" exchange',
            [
                'tags' => [
                    'message_bus.routing_key' => 'none',
                    'message_bus.exchange_name' => 'exchangeName',
                    'span.kind' => 'producer',
                    'auxmoney-opentracing-bundle.span-origin' => 'rabbitmq:producer',
                ]
            ]
        )->shouldBeCalledTimes(1);

        $this->tracingService
            ->injectTracingHeadersIntoCarrier([])
            ->shouldBeCalledTimes(1)
            ->willReturn(['UBER-TRACING-ID' => '123456789']);

        $this->tracingService->finishActiveSpan()->shouldBeCalledTimes(1);

        $this->logger->debug(
            Argument::any(),
            [
                'amqp' => [
                    'body' => 'msgBody',
                    'routingkeys' => '',
                    'properties' => [],
                    'headers' => ['custom' => 'header', 'UBER-TRACING-ID' => '123456789']
                ]
            ]
        )->shouldBeCalledTimes(1);

        $this->subject->publish('msgBody', '', [], ['custom' => 'header']);
    }

    public function testPublishWithRoutingKey(): void
    {
        $this->tracingService->startActiveSpan(
            'RabbitMq: Publishing message to "exchangeName" exchange',
            [
                'tags' => [
                    'message_bus.routing_key' => 'customRouting',
                    'message_bus.exchange_name' => 'exchangeName',
                    'span.kind' => 'producer',
                    'auxmoney-opentracing-bundle.span-origin' => 'rabbitmq:producer',
                ]
            ]
        )->shouldBeCalledTimes(1);

        $this->tracingService
            ->injectTracingHeadersIntoCarrier([])
            ->shouldBeCalledTimes(1)
            ->willReturn(['UBER-TRACING-ID' => '123456789']);

        $this->tracingService->finishActiveSpan()->shouldBeCalledTimes(1);

        $this->logger->debug(
            Argument::any(),
            [
                'amqp' => [
                    'body' => 'msgBody',
                    'routingkeys' => 'customRouting',
                    'properties' => [],
                    'headers' => ['UBER-TRACING-ID' => '123456789']
                ]
            ]
        )->shouldBeCalledTimes(1);

        $this->subject->publish('msgBody', 'customRouting');
    }

    public function testPublishWithAdditionalProperties(): void
    {
        $this->tracingService->startActiveSpan(
            'RabbitMq: Publishing message to "exchangeName" exchange',
            [
                'tags' => [
                    'message_bus.routing_key' => 'none',
                    'message_bus.exchange_name' => 'exchangeName',
                    'span.kind' => 'producer',
                    'auxmoney-opentracing-bundle.span-origin' => 'rabbitmq:producer',
                ]
            ]
        )->shouldBeCalledTimes(1);

        $this->tracingService
            ->injectTracingHeadersIntoCarrier([])
            ->shouldBeCalledTimes(1)
            ->willReturn(['UBER-TRACING-ID' => '123456789']);

        $this->tracingService->finishActiveSpan()->shouldBeCalledTimes(1);

        $this->logger->debug(
            Argument::any(),
            [
                'amqp' => [
                    'body' => 'msgBody',
                    'routingkeys' => '',
                    'properties' => ['additional' => 'property'],
                    'headers' => ['UBER-TRACING-ID' => '123456789']
                ]
            ]
        )->shouldBeCalledTimes(1);

        $this->subject->publish('msgBody', '', ['additional' => 'property']);
    }
}
