# ベースイメージとしてPHP 8.2-FPMを使用
FROM php:8.2-fpm

# 作業ディレクトリを設定
WORKDIR /var/www/html

# Composerをインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# アプリケーションのソースコードをコピー
COPY . /var/www/html

# Composerの依存関係をインストール
RUN composer install

# ポートを公開
EXPOSE 9000

# エントリポイントスクリプトを使用
COPY .docker/docker-entrypoint.sh /usr/local/bin/docker-php-entrypoint
RUN chmod +x /usr/local/bin/docker-php-entrypoint
