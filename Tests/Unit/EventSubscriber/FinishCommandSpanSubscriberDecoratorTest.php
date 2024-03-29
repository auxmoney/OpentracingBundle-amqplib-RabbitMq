<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingAmqplibRabbitMqBundle\Tests\Unit\EventSubscriber;

use Auxmoney\OpentracingAmqplibRabbitMqBundle\EventSubscriber\FinishCommandSpanSubscriberDecorator;
use Auxmoney\OpentracingBundle\EventListener\FinishCommandSpanSubscriber;
use Auxmoney\OpentracingBundle\Internal\Persistence;
use Auxmoney\OpentracingBundle\Service\Tracing;
use OldSound\RabbitMqBundle\Command\BaseConsumerCommand;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FinishCommandSpanSubscriberDecoratorTest extends TestCase
{
    use ProphecyTrait;

    /** @var Tracing|ObjectProphecy */
    private $tracing;
    private FinishCommandSpanSubscriberDecorator $subject;

    public function setUp(): void
    {
        $this->tracing = $this->prophesize(Tracing::class);
        $persistence = $this->prophesize(Persistence::class);

        $decoratedSubscriber = new FinishCommandSpanSubscriber(
            $this->tracing->reveal(),
            $persistence->reveal()
        );

        $this->subject = new FinishCommandSpanSubscriberDecorator($decoratedSubscriber);
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertArrayHasKey('console.terminate', FinishCommandSpanSubscriberDecorator::getSubscribedEvents());
    }

    public function testOnTerminateWithNonBaseConsumerCommand(): void
    {
        $command = $this->prophesize(Command::class);
        $input = $this->prophesize(InputInterface::class);
        $output = $this->prophesize(OutputInterface::class);

        $consoleEvent = new ConsoleTerminateEvent($command->reveal(), $input->reveal(), $output->reveal(), 0);

        $this->tracing->setTagOfActiveSpan('command.exit-code', 0)->shouldBeCalledTimes(1);
        $this->tracing->finishActiveSpan()->shouldBeCalledTimes(1);

        $this->subject->onTerminate($consoleEvent);
    }

    public function testOnTerminateWithBaseConsumerCommand(): void
    {
        $command = $this->prophesize(BaseConsumerCommand::class);
        $input = $this->prophesize(InputInterface::class);
        $output = $this->prophesize(OutputInterface::class);

        $consoleEvent = new ConsoleTerminateEvent($command->reveal(), $input->reveal(), $output->reveal(), 0);

        $this->tracing->finishActiveSpan()->shouldNotBeCalled();

        $this->subject->onTerminate($consoleEvent);
    }
}
