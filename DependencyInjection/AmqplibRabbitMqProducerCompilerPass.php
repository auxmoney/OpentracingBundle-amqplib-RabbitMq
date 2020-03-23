<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingAmqplibRabbitMqBundle\DependencyInjection;

use Auxmoney\OpentracingAmqplibRabbitMqBundle\Producer\Producer;
use Auxmoney\OpentracingBundle\Service\Tracing;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AmqplibRabbitMqProducerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $tracingDefinition = $container->getDefinition(Tracing::class);

        foreach ($container->getDefinitions() as $definition) {
            if (array_key_exists('old_sound_rabbit_mq.producer', $definition->getTags())) {
                $arguments = $definition->getArguments();
                array_unshift($arguments, $tracingDefinition);
                $definition
                    ->setClass(Producer::class)
                    ->setArguments($arguments);
            }
        }
    }
}
