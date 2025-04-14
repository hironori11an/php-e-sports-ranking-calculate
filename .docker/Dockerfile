# ベースイメージとしてPHP 8.2-FPMを使用
FROM php:8.2-fpm

# 作業ディレクトリを設定
WORKDIR /var/www/html

# 必要なパッケージをインストール
RUN apt-get update && apt-get install -y \
    nginx \
    && rm -rf /var/lib/apt/lists/*

# Composerをインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Nginxの設定ファイルをコピー
COPY .docker/nginx.conf /etc/nginx/nginx.conf
COPY .docker/default.conf /etc/nginx/conf.d/default.conf

# アプリケーションのソースコードをコピー
COPY . /var/www/html

# Composerの依存関係をインストール
RUN composer install

# エントリポイントスクリプトを使用
COPY .docker/docker-entrypoint.sh /usr/local/bin/docker-php-entrypoint
RUN chmod +x /usr/local/bin/docker-php-entrypoint
