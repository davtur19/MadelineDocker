FROM composer:latest AS build

WORKDIR /app/lib/madeline/

RUN apk add --update g++ autoconf automake libtool libzip-dev curl-dev gmp-dev oniguruma-dev opus-dev libevent-dev cmake linux-headers nghttp2-libs libffi-dev libsodium-dev icu-dev &&\
    docker-php-ext-install -j$(nproc) curl gmp mbstring json ffi sodium intl sockets

RUN cd /app/lib/madeline &&\
    wget https://pecl.php.net/get/event-2.5.4.tgz &&\
    tar -xf event-2.5.4.tgz &&\
    cd event-2.5.4 &&\
    phpize &&\
    ./configure &&\
    make -j$(nproc) &&\
    make install &&\
    echo "extension=event.so" |tee /usr/local/etc/php/conf.d/event.ini

RUN cd /app/lib/madeline &&\
    git clone --depth=1 https://github.com/CopernicaMarketingSoftware/PHP-CPP &&\
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
    make TGVOIP_LOG_VERBOSITY=0 &&\
    make install

RUN cd /app/lib/madeline &&\
    git clone --depth=1 https://github.com/microsoft/mimalloc &&\
    cd mimalloc &&\
    mkdir release &&\
    cd release &&\
    cmake .. -DCMAKE_BUILD_TYPE=Release &&\
    make -j$(nproc) &&\
    make install
    
RUN apk del gcc g++ autoconf automake libtool cmake libzip-dev curl-dev oniguruma-dev linux-headers &&\
    rm -rf /var/cache/apk/* \
    /app/lib/madeline/PHP-CPP \
    /app/lib/madeline/PrimeModule-ext \
    /app/lib/madeline/php-libtgvoip \
    /app/lib/madeline/event-2.5.4.tgz \
    /app/lib/madeline/event-2.5.4 \
    /app/lib/madeline/mimalloc

RUN cd /app/lib/madeline/ &&\
    composer require danog/madelineproto

WORKDIR /app/src/madeline/

VOLUME /app/src/madeline/

ENTRYPOINT ["bash", "-c","LD_PRELOAD=/usr/lib/libmimalloc.so php main.php"]
