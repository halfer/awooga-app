# Dockerfile for Awooga runtime

FROM alpine:3.8 AS base

# Need PHP for Composer
# Composer needs openssl, json, phar, iconv/mbstring, zip
# Composer recommends zlib
# Need Git for submodules
RUN apk update
RUN apk add \
    php-cli git openssl \
    php-openssl php-json php-mbstring php-phar php-zip
# PHPUnit needs tokenizer, dom, bz2
RUN apk add php-tokenizer php-dom php-bz2

# Get the Git submodules
# @todo There is no hash lock on this, can I use Composer instead?
RUN mkdir /tmp/project/
RUN cd /tmp/project && \
    git init && \
    git submodule add \
        --name htmlpurifier \
        https://github.com/ezyang/htmlpurifier.git modules/htmlpurifier

# Do the Composer stuff
COPY build/composer.sh /tmp/build/composer.sh
RUN cd /tmp/build && sh /tmp/build/composer.sh
COPY composer.json composer.lock /tmp/project/
RUN cd /tmp/project/ && php /tmp/build/composer.phar install

# Do the Bower stuff
RUN apk add npm
RUN cd /tmp/project/ && npm install bower
COPY bower.json /tmp/project/
RUN cd /tmp/project/ && node_modules/bower/bin/bower --allow-root install

# ***
# Run time environment
# ***
FROM alpine:3.8 AS runtime

# Install software
RUN apk update
RUN apk add php-apache2
# Requirements for the PHP runtime
# Debug Toolbar needs 'json'
# Symfony\\Polyfill\\Mbstring needs 'iconv'
RUN apk add php-session php-pdo_mysql php-json php-iconv

WORKDIR /var/www/localhost/htdocs

# Prep Apache
RUN mkdir -p /run/apache2
RUN echo "ServerName localhost" > /etc/apache2/conf.d/server-name.conf
COPY build/apache/rewrite.conf /etc/apache2/conf.d/rewrite.conf
# Change the docroot
RUN sed -i \
    -e 's/\/var\/www\/localhost\/htdocs/\/var\/www\/localhost\/htdocs\/web/g' \
    /etc/apache2/httpd.conf

# Copy source files from the filing system
COPY src src
COPY web web
COPY --from=base /tmp/project/bower_components bower_components

# Copy Git submodules from "base"
COPY --from=base /tmp/project/modules modules

# Copy Composer dependencies from "base"
COPY --from=base /tmp/project/vendor vendor

# Copy config into place
# @todo This config should be injected in, not trapped in code
COPY config/env-config.php.example config/env-config.php

# @todo Add Cron system

# @todo Add healthcheck

# @todo Mount the filesystem

# Start Apache
EXPOSE 80
# @todo Move this to a shell script, blow up if SLIM_MODE does not exist
# @todo Also blow up if docker network alias does not exist
CMD ["/usr/sbin/httpd", "-DFOREGROUND"]

# ***
# Test environment
# ***
FROM alpine:3.8 AS test

# @todo Copy PhantomJS

# @todo Copy tests

# @todo Run tests
