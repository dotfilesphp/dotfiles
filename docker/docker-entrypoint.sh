#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php "$@"
fi

#if [ "$1" = 'php-fpm' ] || [ "$1" = 'bin/dotfiles' ]; then
	#if [ "$APP_ENV" != 'prod' ]; then
		#composer install --prefer-dist --no-progress --no-suggest --no-interaction
		#bin/console assets:install
		#bin/console doctrine:schema:update -f
	#fi

	# Permissions hack because setfacl does not work on Mac and Windows
	#chown -R www-data var
	#

#fi

mkdir -p /home/backup
cd /home/backup
DOTFILES_ENV=dev /app/bin/dotfiles compile /app/build
dotfiles init -m docker /home/backup

export DOTFILES_ENV=prod
dotfiles cache-clear -vvv
echo $PWD
exec docker-php-entrypoint "$@"
