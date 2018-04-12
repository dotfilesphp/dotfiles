#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php "$@"
fi

mkdir -p /home/backup
cd /home/backup
DOTFILES_ENV=dev /app/bin/dotfiles compile /app/build
dotfiles init -m docker /home/backup

export DOTFILES_ENV=prod
dotfiles cache-clear -vvv
echo $PWD
exec docker-php-entrypoint "$@"
