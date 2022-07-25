<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingAmqplibRabbitMqBundle\EventSubscriber;

use Auxmoney\OpentracingAmqplibRabbitMqBundle\Consumer\Consumer;
use Auxmoney\OpentracingBundle\Internal\Constant;
use Auxmoney\OpentracingBundle\Internal\Utility;
use Auxmoney\OpentracingBundle\Service\Tracing;
use OldSound\RabbitMqBundle\Event\AMQPEvent;
use OpenTracing\Reference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use const OpenTracing\Tags\SPAN_KIND;
use const OpenTracing\Tags\SPAN_KIND_MESSAGE_BUS_CONSUMER;

final class BeforeMessageProcessingSubscriber implements EventSubscriberInterface
{
    private const SPAN_NAME = 'RabbitMq: Processing message from "%s" queue';
    private const TAG_QUEUE_NAME = 'message_bus.queue_name';

    private Tracing $tracing;
    private Utility $utility;

    public function __construct(Tracing $tracing, Utility $utility)
    {
        $this->tracing = $tracing;
        $this->utility = $utility;
    }

    /**
     * @return array<string,string>
     */
    public static function getSubscribedEvents(): array
    {
        return [AMQPEvent::BEFORE_PROCESSING_MESSAGE => 'onBeforeMessageProcessing'];
    }

    public function onBeforeMessageProcessing(AMQPEvent $amqpEvent): void
    {
        $spanOptions = $this->getSpanOptions($amqpEvent);
        $this->tracing->startActiveSpan(
            sprintf(self::SPAN_NAME, $spanOptions['tags'][self::TAG_QUEUE_NAME]),
            $spanOptions
        );
    }

    /**
     * @param AMQPEvent $amqpEvent
     *
     * @return array<string,mixed>
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getSpanOptions(AMQPEvent $amqpEvent): array
    {
        $options = [];
        $options['tags'][SPAN_KIND] = SPAN_KIND_MESSAGE_BUS_CONSUMER;
        $options['tags'][Constant::SPAN_ORIGIN] = 'rabbitmq:consumer';

        /** @var Consumer $consumer */
        $consumer = $amqpEvent->getConsumer();
        $queueOptions = $consumer->getQueueOptions();
        $options['tags'][self::TAG_QUEUE_NAME] = $queueOptions['name'];

        $messageProperties = $amqpEvent->getAMQPMessage()->get_properties();
        if (array_key_exists('application_headers', $messageProperties)) {
            $applicationHeaders = $messageProperties['application_headers']->getNativeData();
            $externalSpanContext = $this->utility->extractSpanContext($applicationHeaders);
            if ($externalSpanContext) {
                $options['references'] = new Reference(Reference::FOLLOWS_FROM, $externalSpanContext);
            }
        }

        return $options;
    }
}
