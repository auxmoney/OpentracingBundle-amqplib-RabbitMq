# auxmoney OpentracingBundle - amqplib/RabbitMq

[![test](https://github.com/auxmoney/OpentracingBundle-amqplib-RabbitMq/workflows/test/badge.svg)](https://github.com/auxmoney/OpentracingBundle-amqplib-RabbitMq/actions?query=workflow%3Atest)
![GitHub release (latest SemVer)](https://img.shields.io/github/v/release/auxmoney/OpentracingBundle-amqplib-RabbitMq)
![Coveralls github](https://img.shields.io/coveralls/github/auxmoney/OpentracingBundle-amqplib-RabbitMq)
![Codacy Badge](https://api.codacy.com/project/badge/Grade/0f9a9d8ae1084efaa11ec443ca426a75)
![Code Climate maintainability](https://img.shields.io/codeclimate/maintainability/auxmoney/OpentracingBundle-amqplib-RabbitMq)
![Scrutinizer code quality (GitHub/Bitbucket)](https://img.shields.io/scrutinizer/quality/g/auxmoney/OpentracingBundle-amqplib-RabbitMq)
![GitHub](https://img.shields.io/github/license/auxmoney/OpentracingBundle-amqplib-RabbitMq)

This bundle adds automatic tracing header propagation and spanning for [RabbitMq](https://github.com/php-amqplib/RabbitMqBundle) producers 
and consumers to the [OpentracingBundle](https://github.com/auxmoney/OpentracingBundle-core).

## Installation

### Prerequisites

This bundle is only an additional plugin and should not be installed independently. See
[its documentation](https://github.com/auxmoney/OpentracingBundle-core#installation) for more information on installing the OpentracingBundle first.

### Require dependencies

After you have installed the OpentracingBundle:

  * require the dependencies:

```bash
    composer req auxmoney/opentracing-bundle-amqplib-rabbitmq
```

### Enable the bundle

If you are using [Symfony Flex](https://github.com/symfony/flex), you are all set!

If you are not using it, you need to manually enable the bundle:

  * add bundle to your application:

```php
    # Symfony 3: AppKernel.php
    $bundles[] = new Auxmoney\OpentracingAmqplibRabbitMqBundle\OpentracingAmqplibRabbitMqBundle();
```

```php
    # Symfony 4: bundles.php
    Auxmoney\OpentracingAmqplibRabbitMqBundle\OpentracingAmqplibRabbitMqBundle::class => ['all' => true],
```

## Configuration

No configuration is necessary, the bundle extension will automatically decorate configured consumers and producers.

## Usage

Whenever a message is produced or consumed, a span is automatically added to the existing trace. The tracing headers are automatically
propagated to the consumer with message headers.

## Development

Be sure to run

```bash
    composer run-script quality
```

every time before you push code changes. The tools run by this script are also run in the CI pipeline.
