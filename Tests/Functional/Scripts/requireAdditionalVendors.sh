#!/bin/bash
shopt -s extglob

cd build/testproject/
composer config extra.symfony.allow-contrib true
composer require auxmoney/opentracing-bundle-amqplib-rabbitmq
rm -fr vendor/auxmoney/opentracing-bundle-amqplib-rabbitmq/*
cp -r ../../!(build|vendor) vendor/auxmoney/opentracing-bundle-amqplib-rabbitmq
composer dump-autoload
cd ../../
