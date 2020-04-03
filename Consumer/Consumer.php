<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingAmqplibRabbitMqBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\Consumer as AmqplibConsumer;

final class Consumer extends AmqplibConsumer
{
    /**
     * @return array<string,mixed>
     */
    public function getQueueOptions(): array
    {
        return $this->queueOptions;
    }
}
