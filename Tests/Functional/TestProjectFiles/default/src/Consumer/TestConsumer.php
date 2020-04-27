<?php

declare(strict_types=1);

namespace App\Consumer;

use Auxmoney\OpentracingBundle\Service\Tracing;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class TestConsumer implements ConsumerInterface
{
    private $tracing;

    public function __construct(Tracing $tracing)
    {
        $this->tracing = $tracing;
    }

    public function execute(AMQPMessage $msg)
    {
        $this->tracing->startActiveSpan('manual consumer span');
        $this->tracing->setTagOfActiveSpan('test.tag', 'manual');
        usleep(10000);
        $this->tracing->finishActiveSpan();

        return self::MSG_ACK;
    }
}
