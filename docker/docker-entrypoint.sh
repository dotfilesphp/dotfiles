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
export COMPOSE_INTERACTIVE_NO_CLI=1
cd /app
DOTFILES_ENV=dev /app/bin/dotfiles compile
mkdir -p /home/dotfiles
mkdir -p /home/backup
cd /home/backup
dotfiles init -m docker /home/backup

exec docker-php-entrypoint "$@"
