FROM php:7.2.3-fpm-alpine

RUN apk add --no-cache --virtual .persistent-deps \
		git \
		zlib \
		bash

RUN set -xe \
	&& apk add --no-cache --virtual .build-deps \ 
	$PHPIZE_DEPS

RUN pecl install xdebug

RUN apk del .build-deps

COPY 99-custom.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

WORKDIR /app

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]

RUN mkdir -p var/cache var/logs var/sessions \
    && chown -R www-data var
