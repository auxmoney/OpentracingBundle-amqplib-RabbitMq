<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingAmqplibRabbitMqBundle\EventSubscriber;

use Auxmoney\OpentracingBundle\Internal\Persistence;
use Auxmoney\OpentracingBundle\Service\Tracing;
use OldSound\RabbitMqBundle\Event\AMQPEvent;
use OldSound\RabbitMqBundle\Event\BeforeProcessingMessageEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AfterMessageProcessingSubscriber implements EventSubscriberInterface
{
    private $tracing;
    private $persistence;

    public function __construct(Tracing $tracing, Persistence $persistence)
    {
        $this->tracing = $tracing;
        $this->persistence = $persistence;
    }

    /**
     * @return array<string,string>
     */
    public static function getSubscribedEvents()
    {
        return [BeforeProcessingMessageEvent::AFTER_PROCESSING_MESSAGE => 'onAfterMessageProcessing'];
    }

    public function onAfterMessageProcessing(AMQPEvent $AMQPEvent): void
    {
        $this->tracing->finishActiveSpan();
        $this->persistence->flush();
    }
}
