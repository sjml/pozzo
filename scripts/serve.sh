#!/usr/bin/env bash

cd "$(dirname "$0")"
cd ..

conf="./scripts/configs/no-debug.php.ini"
if [[ $1 = "debug" ]]; then
  conf="./scripts/configs/debug.php.ini"
fi

cmd="php -c $conf -S 0.0.0.0:8080 -t ./public/ ./server.php"

export PHP_CLI_SERVER_WORKERS=4
if [[ $1 = "debug" ]]; then
  $cmd
else
  $cmd 2>&1 >/dev/null | grep -v -E '(Accepted|Closing)$'
fi


