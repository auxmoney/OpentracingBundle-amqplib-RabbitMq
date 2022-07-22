<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingAmqplibRabbitMqBundle\Tests\Unit\EventSubscriber;

use Auxmoney\OpentracingAmqplibRabbitMqBundle\EventSubscriber\StartCommandSpanSubscriberDecorator;
use Auxmoney\OpentracingBundle\EventListener\StartCommandSpanSubscriber;
use Auxmoney\OpentracingBundle\Factory\SpanOptionsFactory;
use Auxmoney\OpentracingBundle\Service\Tracing;
use OldSound\RabbitMqBundle\Command\BaseConsumerCommand;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StartCommandSpanSubscriberDecoratorTest extends TestCase
{
    use ProphecyTrait;

    /** @var Tracing|ObjectProphecy */
    private $tracing;
    /** @var SpanOptionsFactory|ObjectProphecy */
    private $spanOptionsFactory;
    /** @var StartCommandSpanSubscriber|ObjectProphecy */
    private $decoratedSubscriber;
    /** @var StartCommandSpanSubscriberDecorator */
    private $subject;

    public function setUp(): void
    {
        $this->tracing = $this->prophesize(Tracing::class);
        $this->spanOptionsFactory = $this->prophesize(SpanOptionsFactory::class);

        $this->decoratedSubscriber = new StartCommandSpanSubscriber(
            $this->tracing->reveal(),
            $this->spanOptionsFactory->reveal()
        );

        $this->subject = new StartCommandSpanSubscriberDecorator($this->decoratedSubscriber);
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertArrayHasKey('console.command', StartCommandSpanSubscriberDecorator::getSubscribedEvents());
    }

    public function testOnCommandWithNonBaseConsumerCommand(): void
    {
        $command = $this->prophesize(Command::class);
        $command->getName()->willReturn('command name');
        $command->getDescription()->willReturn('command description');
        $input = $this->prophesize(InputInterface::class);
        $output = $this->prophesize(OutputInterface::class);

        $consoleEvent = new ConsoleEvent($command->reveal(), $input->reveal(), $output->reveal());

        $this->spanOptionsFactory->createSpanOptions()->shouldBeCalledTimes(1)->willReturn([]);

        $this->subject->onCommand($consoleEvent);
    }

    public function testOnCommandWithBaseConsumerCommand(): void
    {
        $command = $this->prophesize(BaseConsumerCommand::class);
        $input = $this->prophesize(InputInterface::class);
        $output = $this->prophesize(OutputInterface::class);

        $consoleEvent = new ConsoleEvent($command->reveal(), $input->reveal(), $output->reveal());

        $this->spanOptionsFactory->createSpanOptions()->shouldNotBeCalled();

        $this->subject->onCommand($consoleEvent);
    }
}
