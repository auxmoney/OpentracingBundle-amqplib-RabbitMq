<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingAmqplibRabbitMqBundle\Producer;

use Auxmoney\OpentracingBundle\Service\Tracing;
use OldSound\RabbitMqBundle\RabbitMq\Producer as AmqplibProducer;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use const OpenTracing\Tags\SPAN_KIND;
use const OpenTracing\Tags\SPAN_KIND_MESSAGE_BUS_PRODUCER;

/**
 * Unfortunately, decorating the AmqplibProducer is not an option since other processes from the oldsound library
 * rely on having a BaseAmqp class injected instead of using an interface
 */
final class Producer extends AmqplibProducer
{
    private const SPAN_NAME = 'Publishing message to "%s" exchange';
    private const TAG_EXCHANGE_NAME = 'ExchangeName';
    private const TAG_ROUTING_KEY = 'RoutingKey';

    private $tracingService;

    public function __construct(
        Tracing $tracingService,
        AbstractConnection $conn,
        AMQPChannel $ch = null,
        $consumerTag = null
    ) {
        parent::__construct($conn, $ch, $consumerTag);
        $this->tracingService = $tracingService;
    }

    /**
     * @param string $msgBody
     * @param string $routingKey
     * @param array<string,mixed> $additionalProperties
     * @param array<string,mixed> $headers
     * @return void
     */
    public function publish($msgBody, $routingKey = '', $additionalProperties = array(), array $headers = null): void
    {
        $exchangeName = $this->exchangeOptions['name'];

        $options = [
            'tags' => [
                self::TAG_ROUTING_KEY => $routingKey ? $routingKey : 'none',
                self::TAG_EXCHANGE_NAME => $exchangeName,
                SPAN_KIND => SPAN_KIND_MESSAGE_BUS_PRODUCER
            ]
        ];

        $this->tracingService->startActiveSpan(sprintf(self::SPAN_NAME, $exchangeName), $options);

        if ($headers === null) {
            $headers = [];
        }
        $headers = array_merge($headers, $this->tracingService->injectTracingHeadersIntoCarrier([]));

        parent::publish($msgBody, $routingKey, $additionalProperties, $headers);
        $this->tracingService->finishActiveSpan();
    }
}
