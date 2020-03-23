<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingAmqplibRabbitMqBundle\DependencyInjection;

use Auxmoney\OpentracingAmqplibRabbitMqBundle\Consumer\Consumer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class AmqplibRabbitMqConsumerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $id => $definition) {
            if (array_key_exists('old_sound_rabbit_mq.consumer', $definition->getTags())) {
                $definition
                    ->setClass(Consumer::class);
            }
        }
    }
}
