<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingAmqplibRabbitMqBundle\Producer;

use Auxmoney\OpentracingBundle\Service\Tracing;
use OldSound\RabbitMqBundle\RabbitMq\Producer as AmqplibProducer;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;

/**
 * Unfortunately, decorating the AmqplibProducer is not an option since other processes from the oldsound library
 * rely on having a BaseAmqp class injected instead of using an interface
 */
class Producer extends AmqplibProducer
{
    private const SPAN_NAME = 'Publishing message to "%s" exchange';

    private $tracingService;

    public function __construct(Tracing $tracingService, AbstractConnection $conn, AMQPChannel $ch = null, $consumerTag = null)
    {
        parent::__construct($conn, $ch, $consumerTag);
        $this->tracingService = $tracingService;
    }

    public function publish($msgBody, $routingKey = '', $additionalProperties = array(), array $headers = null)
    {
        $this->tracingService->startActiveSpan(sprintf(self::SPAN_NAME, $this->exchangeOptions['name']));
        $this->tracingService->setTagOfActiveSpan('RoutingKey', $routingKey ? $routingKey : 'none');
        if ($headers === null) {
            $headers = [];
        }
        $headers = array_merge($headers, $this->tracingService->injectTracingHeadersIntoCarrier([]));

        parent::publish($msgBody, $routingKey, $additionalProperties, $headers);
        $this->tracingService->finishActiveSpan();
    }
}
