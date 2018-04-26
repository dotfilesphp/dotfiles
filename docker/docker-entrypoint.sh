#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php "$@"
fi

mkdir -p /home/backup
cd /home/backup
cp /home/dotfiles/behat.yml.dist /app/
DOTFILES_ENV=dev /home/dotfiles/bin/tools compile /app/build
chdir /home/backup
echo $PWD
dotfiles init -m docker /home/backup -vvv
dotfiles cache-clear -vvv
exec docker-php-entrypoint "$@"
