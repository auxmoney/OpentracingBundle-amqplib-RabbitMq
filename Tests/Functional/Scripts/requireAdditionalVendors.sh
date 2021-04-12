#!/bin/bash
shopt -s extglob

cd build/testproject/
composer config extra.symfony.allow-contrib true
SUFFIX=""
if [ -n "$GITHUB_REF" ];
then
    SUFFIX=:dev-${GITHUB_REF##*/}
fi
if [ -n "$GITHUB_HEAD_REF" ];
then
    SUFFIX=:dev-${GITHUB_HEAD_REF##*/}
fi
composer require auxmoney/opentracing-bundle-amqplib-rabbitmq${SUFFIX}
rm -fr vendor/auxmoney/opentracing-bundle-amqplib-rabbitmq/*
cp -r ../../!(build|vendor) vendor/auxmoney/opentracing-bundle-amqplib-rabbitmq
composer dump-autoload
cd ../../
