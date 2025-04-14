#!/bin/sh

# 開発環境の場合のみ、Xdebugを有効にする
if [ "$APP_ENV" = "development" ] &&
  [ ! -e "/usr/local/etc/php/conf.d/xdebug.ini" ] ; then 
    pecl install xdebug && \
    docker-php-ext-enable xdebug
    # Config XDebug
    echo "
        xdebug.mode=debug,coverage
        xdebug.client_host=host.docker.internal
        xdebug.client_port=9003
        xdebug.start_with_request=yes
        xdebug.discover_client_host=1
        " > /usr/local/etc/php/conf.d/xdebug.ini;
fi

# Nginxを起動
service nginx start

# PHP-FPMを起動
php-fpm 