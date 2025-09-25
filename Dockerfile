FROM ubuntu:22.04

WORKDIR /var/www

ARG WWWGROUP
ARG WWWUSER

ENV DEBIAN_FRONTEND=noninteractive

# Install common tools
RUN apt-get update && apt-get upgrade -y && apt-get install -y \
    curl \
    unzip \
    supervisor \
    git \
    dbus \
    apt-utils \
    make

# Install system dependencies
RUN apt-get update && apt upgrade -y && apt-get install -y \
    --no-install-recommends php8.1 \
    php8.1-cli \
    php8.1-fpm \
    php8.1-mbstring \
    php8.1-bcmath \
    php8.1-xml \
    php8.1-xmlrpc \
    php8.1-zip \
    php8.1-sqlite3 \
    php8.1-mysql \
    php8.1-pgsql \
    php8.1-imap \
    php8.1-readline \
    php8.1-phpdbg \
    php8.1-curl \
    php8.1-dev \
    php-pear \
    php-ssh2 \
    php-yaml \
    php-memcached \
    php-redis \
    php-apcu \
    php-xdebug \
    php-dev \
    php-gd \
    --reinstall ca-cacert ca-certificates

RUN pecl install mongodb-1.20.0

RUN echo "extension=mongodb.so" > /etc/php/8.1/cli/conf.d/20-mongodb.ini
RUN echo "extension=mongodb.so" > /etc/php/8.1/fpm/conf.d/20-mongodb.ini

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Get latest Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

COPY . .

RUN bash -c 'mkdir -p /var/www/storage/{app,logs}'
RUN bash -c 'mkdir -p /var/www/storage/framework/{cache,sessions,testing,views}'

RUN bash -c 'chmod -R 775 /var/www/storage/{app,logs}'
RUN bash -c 'chmod -R 775 /var/www/storage/framework/{cache,sessions,testing,views}'

COPY infrastructure/start-container /usr/local/bin/start-container
COPY infrastructure/supervisord.conf /etc/supervisor/supervisord.sail.conf
COPY infrastructure/php/php.ini /etc/php/8.1/cli/conf.d/99-sail.ini
COPY infrastructure/php/www.conf /etc/php/8.1/fpm/pool.d/www.conf

RUN if getent group $WWWGROUP >/dev/null; then \
      echo "Group with GID=$WWWGROUP will be renamed on schedule-bot"; \
      groupmod -n schedule-bot $(getent group $WWWGROUP | cut -d: -f1); \
    else \
      groupadd -g $WWWGROUP schedule-bot; \
    fi \
 && useradd -u $WWWUSER -g $WWWGROUP -s /bin/bash schedule-bot

# Chown all the files to the app user.
RUN chown -R schedule-bot:schedule-bot /var/www

RUN chown -R schedule-bot:schedule-bot storage bootstrap/cache
RUN chmod -R ug+rwX storage bootstrap/cache

RUN chmod +x /usr/local/bin/start-container

RUN composer install

EXPOSE 7000

# USER schedule-bot

ENTRYPOINT [ "/usr/local/bin/start-container"]