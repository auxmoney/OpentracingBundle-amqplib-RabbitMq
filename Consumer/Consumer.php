<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingAmqplibRabbitMqBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\Consumer as AmqplibProducer;

final class Consumer extends AmqplibProducer
{
    /**
     * @return array<string,mixed>
     */
    public function getQueueOptions(): array
    {
        return $this->queueOptions;
    }
}
