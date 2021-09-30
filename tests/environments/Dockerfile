ARG TAG=8.0-apache
FROM registry.us-west-1.aliyuncs.com/vistart_public/php:${TAG}

LABEL maintainer="vistart <i@vistart.me>"

RUN wget https://phar.phpunit.de/phpunit-9.phar -O /usr/bin/phpunit && chmod +x /usr/bin/phpunit