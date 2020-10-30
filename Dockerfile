FROM php:7.4-apache
COPY . /var/www/html
RUN DEBIAN_FRONTEND=noninteractive apt-get update && apt-get install -y wget gnupg iputils-ping iproute2 curl
RUN DEBIAN_FRONTEND=noninteractive apt-get install -y \
    libpq-dev \
    libmemcached-dev \
    libmemcached-tools \
    zlibc \
    zlib1g \
    cron \
    libxml2-dev \
    debian-archive-keyring \
    libfreetype6-dev \
    libmcrypt-dev \
    lsb-release \
    libgmp-dev \
    zlib1g-dev \
    libgeoip-dev \
    expect-dev \
    git \
    nano \
    python \
    locales \
    libmcrypt-dev \
    libzip-dev \
    && ln -s /usr/include/x86_64-linux-gnu/gmp.h /usr/include/gmp.h \
    && docker-php-ext-install -j$(nproc) pdo_mysql bcmath zip gmp soap
RUN set -ex \
    && rm -rf /var/lib/apt/lists/* \
    && MEMCACHED="`mktemp -d`" \
    && curl -skL https://github.com/php-memcached-dev/php-memcached/archive/master.tar.gz | tar zxf - --strip-components 1 -C $MEMCACHED \
    && docker-php-ext-configure $MEMCACHED \
    && docker-php-ext-install $MEMCACHED \
    && rm -rf $MEMCACHED
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN a2enmod rewrite && \
    service apache2 restart && \
    chown www-data:www-data -R /var/www/html
ADD crons/crontab /etc/cron.d/root
RUN chmod 0644 /etc/cron.d/root
RUN crontab /etc/cron.d/root
RUN touch /var/log/cron.log
CMD ( cron -f -l 8 & ) && apache2-foreground
EXPOSE 80 3306 443 11211