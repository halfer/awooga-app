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
COPY .gitmodules /tmp/project/
RUN cd /tmp/project && \
    git init && \
    git submodule add --name modules/htmlpurifier https://github.com/ezyang/htmlpurifier.git

# Do the composer stuff
COPY build/composer.sh /tmp/build/composer.sh
RUN cd /tmp/build && sh /tmp/build/composer.sh
COPY composer.json composer.lock /tmp/project/
RUN cd /tmp/project/ && php /tmp/build/composer.phar install

# ***
# Run time environment
# ***
FROM alpine:3.8 AS runtime

WORKDIR /var/www/localhost

# Install software
RUN apk update
RUN apk add php-apache2

# Prep Apache
RUN mkdir -p /run/apache2
RUN echo "ServerName localhost" > /etc/apache2/conf.d/server-name.conf

# Copy source files from the filing system
COPY src src
COPY web web

# Copy Git submodules from "base"
# @todo Why is the source not found?
#COPY --from=base /tmp/project/modules modules

# Copy Composer dependencies from "base"
COPY --from=base /tmp/project/vendor vendor

# @todo Add custom Apache config

# @todo Copy config into place

# @todo Add Cron system

# @todo Add healthcheck

# Start Apache
EXPOSE 80
CMD ["/usr/sbin/httpd", "-DFOREGROUND"]

# ***
# Test environment
# ***
FROM alpine:3.8 AS test

# @todo Copy PhantomJS

# @todo Copy tests

# @todo Run tests
