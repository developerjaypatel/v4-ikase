FROM php:7.4-cli-alpine
WORKDIR /home/www-data

## Installs Composer
RUN curl -sS https://getcomposer.org/installer \
    | php -- --filename=composer \
    --install-dir=/usr/local/bin
RUN mkdir -m777 /.composer

## Installs extensions dependencies + git (used by some edge composer packages)
RUN apk add --no-cache \
        git \
        libpng-dev \
        curl-dev \
#        zlib-dev \
        libzip-dev \
        libxml2-dev \
        g++ autoconf make \
        bzip2-dev libintl icu icu-dev libxslt-dev gettext-dev \
    && rm -rf /var/cache/apk/* \
    && find / -type f -iname \*.apk-new -delete \
    && rm -rf /var/cache/apk/*

# Installs needed extensions
RUN docker-php-ext-install gd zip pdo_mysql json pdo fileinfo simplexml xmlrpc

# Installs "default"? extensions
RUN docker-php-ext-install bz2 calendar ctype exif gettext iconv intl phar posix shmop soap sockets sysvmsg sysvsem sysvshm tokenizer xml xmlwriter xsl mysqli

# also "default", but with a weird issue: https://github.com/docker-library/php/issues/373
RUN CFLAGS="-I/usr/src/php" docker-php-ext-install dom xmlreader

# probably needed/default but didn't work out of the box
#RUN pecl install redis apcu \
#    && docker-php-ext-enable redis apcu

# Those extensions are enabled by default, but they're here just for the sake of it
#RUN docker-php-ext-install curl ftp mbstring mysqlnd openssl sqlite3 zlib pdo_sqlite

## TODO: https://gist.github.com/mikecrittenden/ebd48da243dc9b253da1726429d12b8f
# RUN pecl install https://github.com/igorsantos07/SPL_Types/archive/0.5.2.tar.gz && docker-php-ext-enable spl_types

RUN apk add --no-cache imagemagick-dev \
    && pecl install xdebug imagick \
    && docker-php-ext-enable xdebug imagick

# TODO: find a more elegant way to put composer dependency binaries in $PATH; 755 at /root so the other user can access binaries there
RUN chmod 755 /root \
    && ln -s /root/.composer/vendor/bin/* /usr/local/bin/
