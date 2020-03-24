<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingAmqplibRabbitMqBundle\Tests\Unit\Consumer;

use Auxmoney\OpentracingAmqplibRabbitMqBundle\Consumer\Consumer;
use PhpAmqpLib\Connection\AbstractConnection;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class ConsumerTest extends TestCase
{
    /** @var Consumer */
    private $subject;

    /** @var AbstractConnection|ObjectProphecy */
    private $conn;

    public function setUp(): void
    {
        $this->conn = $this->prophesize(AbstractConnection::class);
        $this->subject = new Consumer($this->conn->reveal());
    }

    public function testGetQueueOptions(): void
    {
        $testOptionsArray = [
            'foo1' => 'bar1',
            'foo2' => 'bar2',
            'foo3' => 'bar3',
            'foo4' => 'bar4',
            'foo5' => 'bar5'
        ];

        $this->subject->setQueueOptions($testOptionsArray);

        $testOptionsArray = array_merge(
            [
                'name' => '',
                'passive' => false,
                'durable' => true,
                'exclusive' => false,
                'auto_delete' => false,
                'nowait' => false,
                'arguments' => null,
                'ticket' => null,
                'declare' => true,
            ],
            $testOptionsArray
        );

        self::assertSame($testOptionsArray, $this->subject->getQueueOptions());
    }
}
