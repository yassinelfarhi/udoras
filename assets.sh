#!/bin/sh
#doctrine cache clear
php app/console doctrine:cache:clear-metadata
php app/console doctrine:cache:clear-query
php app/console doctrine:cache:clear-result

php app/console cache:clear --env=prod --no-debug
php app/console cache:clear

php app/console doctrine:schema:update --force

php app/console doctrine:migrations:migrate --no-interaction

php app/console doctrine:schema:validate

chmod -R 777 app/cache
chmod -R 777 app/logs
chmod -R 777 app/spool

php app/console assets:install --env=prod --no-debug --relative --symlink
php app/console assets:install --relative --symlink
php app/console assetic:dump --env=prod --no-debug
php app/console assetic:dump
chmod -R 777 web/

php app/console cache:warmup --env=prod --no-debug
php app/console cache:warmup


php app/console mopa:bootstrap:install:font
