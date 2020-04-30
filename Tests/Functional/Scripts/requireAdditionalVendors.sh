#!/bin/bash

cd build/testproject/
composer config extra.symfony.allow-contrib true
composer config repositories.origin vcs https://github.com/${PR_ORIGIN}
composer require auxmoney/opentracing-bundle-amqplib-rabbitmq:dev-${BRANCH}
cd ../../
