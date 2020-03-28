<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingAmqplibRabbitMqBundle;

use Auxmoney\OpentracingAmqplibRabbitMqBundle\DependencyInjection\AmqplibRabbitMqConsumerCompilerPass;
use Auxmoney\OpentracingAmqplibRabbitMqBundle\DependencyInjection\AmqplibRabbitMqProducerCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class OpentracingAmqplibRabbitMqBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(
            new AmqplibRabbitMqProducerCompilerPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            -999
        );
        $container->addCompilerPass(
            new AmqplibRabbitMqConsumerCompilerPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            -999
        );
    }
}
