<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingAmqplibRabbitMqBundle\EventSubscriber;

use Auxmoney\OpentracingBundle\EventListener\FinishCommandSpanSubscriber;
use OldSound\RabbitMqBundle\Command\BaseConsumerCommand;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class FinishCommandSpanSubscriberDecorator implements EventSubscriberInterface
{
    private FinishCommandSpanSubscriber $decoratedSubscriber;

    public function __construct(FinishCommandSpanSubscriber $decoratedSubscriber)
    {
        $this->decoratedSubscriber = $decoratedSubscriber;
    }

    /**
     * @return array<string,array<int,mixed>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'console.terminate' => ['onTerminate', -2048],
        ];
    }

    public function onTerminate(ConsoleTerminateEvent $event): void
    {
        if ($event->getCommand() instanceof BaseConsumerCommand) {
            return;
        }
        $this->decoratedSubscriber->onTerminate($event);
    }
}
