FROM php:fpm-alpine

RUN apk add --no-cache --virtual .persistent-deps \
		git \
		zlib \
		bash

RUN set -xe \
	&& apk add --no-cache --virtual .build-deps \
	$PHPIZE_DEPS

RUN pecl install xdebug

#RUN apk del .build-deps

COPY custom.ini /usr/local/etc/php/conf.d/99-custom.ini

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint


ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]
RUN mkdir -p /app/var
RUN ln -s /app/build/dotfiles.phar /usr/local/bin/dotfiles
RUN ln -s /home/dotfiles/src/Core/Resources/bin/compile-dotfiles /usr/local/bin/compile-dotfiles

WORKDIR /app
