FROM composer:latest AS build

WORKDIR /app/lib/madeline/

RUN apk add --update g++ autoconf automake libtool libzip-dev curl-dev gmp-dev oniguruma-dev opus-dev &&\
    docker-php-ext-install -j$(nproc) curl gmp mbstring json

RUN cd /app/lib/madeline &&\
    git clone --depth=1 http://github.com/CopernicaMarketingSoftware/PHP-CPP &&\
    cd /app/lib/madeline/PHP-CPP &&\
    make -j$(nproc) &&\
    make install

RUN cd /app/lib/madeline &&\
    git clone --depth=1 https://github.com/danog/PrimeModule-ext &&\
    cd PrimeModule-ext &&\
    make -j$(nproc) &&\
    make install

RUN cd /app/lib/madeline &&\
    git clone --depth=1 --recursive http://github.com/danog/php-libtgvoip &&\
    cd php-libtgvoip &&\
    sed 's/sudo //g' -i Makefile &&\
    make &&\
    make install

RUN apk del gcc g++ autoconf automake libtool libzip-dev curl-dev oniguruma-dev &&\
    rm -rf /var/cache/apk/* \
    /app/lib/madeline/PHP-CPP \
    /app/lib/madeline/PrimeModule-ext \
    /app/lib/madeline/php-libtgvoip

RUN cd /app/lib/madeline/ &&\
    composer require danog/madelineproto

WORKDIR /app/src/madeline/

VOLUME /app/src/madeline/

ENTRYPOINT ["bash", "-c","php main.php"]
