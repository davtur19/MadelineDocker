FROM composer:latest

WORKDIR /app/lib/

RUN apk add --update libzip-dev curl-dev gmp-dev oniguruma-dev &&\
    docker-php-ext-install -j$(nproc) curl gmp mbstring json &&\
    apk del gcc g++ &&\
    rm -rf /var/cache/apk/*

RUN git clone https://github.com/danog/MadelineProto

RUN composer update -d MadelineProto/

WORKDIR /app/src/madeline/

ENTRYPOINT ["php", "main.php"]
