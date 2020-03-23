<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingAmqplibRabbitMqBundle\EventSubscriber;

use Auxmoney\OpentracingBundle\EventListener\StartCommandSpanSubscriber;
use OldSound\RabbitMqBundle\Command\BaseConsumerCommand;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class StartCommandSpanSubscriberDecorator implements EventSubscriberInterface
{
    private $startCommandSpanSubscriber;

    public function __construct(StartCommandSpanSubscriber $startCommandSpanSubscriber)
    {
        $this->startCommandSpanSubscriber = $startCommandSpanSubscriber;
    }

    /**
     * @return array<string,array>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'console.command' => ['onCommand', 4096],
        ];
    }

    public function onCommand(ConsoleEvent $event): void
    {
        if ($event->getCommand() instanceof BaseConsumerCommand) {
            return;
        }
        $this->startCommandSpanSubscriber->onCommand($event);
    }
}
