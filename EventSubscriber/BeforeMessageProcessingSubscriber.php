<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingAmqplibRabbitMqBundle\EventSubscriber;

use OldSound\RabbitMqBundle\Event\AMQPEvent;
use OldSound\RabbitMqBundle\Event\BeforeProcessingMessageEvent;
use PhpAmqpLib\Wire\AMQPTable;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BeforeMessageProcessingSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [BeforeProcessingMessageEvent::BEFORE_PROCESSING_MESSAGE => 'onBeforeMessageProcessing'];
    }

    public function onBeforeMessageProcessing(AMQPEvent $AMQPEvent)
    {
        /** @var AMQPTable $table */
        $table = $AMQPEvent->getAMQPMessage()->get_properties()['application_headers'];
        $uberTracingHeader = $table->getNativeData();

        var_dump($uberTracingHeader);
    }
}
