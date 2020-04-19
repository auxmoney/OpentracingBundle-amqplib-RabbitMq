#!/bin/bash

cd build/testproject/
composer config extra.symfony.allow-contrib true
composer require auxmoney/opentracing-bundle-amqplib-rabbitmq:${BRANCH}
cd ../../
