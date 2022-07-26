<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingAmqplibRabbitMqBundle\Tests\Unit\EventSubscriber;

use Auxmoney\OpentracingAmqplibRabbitMqBundle\EventSubscriber\AfterMessageProcessingSubscriber;
use Auxmoney\OpentracingBundle\Internal\Persistence;
use Auxmoney\OpentracingBundle\Service\Tracing;
use OldSound\RabbitMqBundle\Event\AMQPEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class AfterMessageProcessingSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /** @var Tracing|ObjectProphecy */
    private $tracing;
    /** @var Persistence|ObjectProphecy */
    private $persistence;
    private AfterMessageProcessingSubscriber $subject;

    public function setUp(): void
    {
        $this->tracing = $this->prophesize(Tracing::class);
        $this->persistence = $this->prophesize(Persistence::class);

        $this->subject = new AfterMessageProcessingSubscriber($this->tracing->reveal(), $this->persistence->reveal());
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertArrayHasKey('after_processing', AfterMessageProcessingSubscriber::getSubscribedEvents());
    }

    public function testOnAfterMessageProcessing(): void
    {
        $this->tracing->finishActiveSpan()->shouldBeCalledTimes(1);
        $this->persistence->flush()->shouldBeCalledTimes(1);

        $this->subject->onAfterMessageProcessing(new AMQPEvent());
    }
}
