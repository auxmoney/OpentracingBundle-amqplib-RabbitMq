<?php

declare(strict_types=1);

namespace App\Command;

use Auxmoney\OpentracingBundle\Internal\Opentracing;
use Auxmoney\OpentracingBundle\Service\Tracing;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use const OpenTracing\Formats\TEXT_MAP;

class TestCommand extends Command
{
    private $opentracing;
    private $tracing;
    private $producer;

    public function __construct(Opentracing $opentracing, Tracing $tracing, ProducerInterface $producer)
    {
        parent::__construct('test:message');
        $this->setDescription('some fancy command description');
        $this->opentracing = $opentracing;
        $this->tracing = $tracing;
        $this->producer = $producer;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->tracing->setTagOfActiveSpan('test.tag', 'command');

        $this->producer->publish('some message');

        $carrier = [];
        $this->opentracing->getTracerInstance()->inject($this->opentracing->getTracerInstance()->getActiveSpan()->getContext(), TEXT_MAP, $carrier);
        $output->writeln(current($carrier));
        return 0;
    }
}
