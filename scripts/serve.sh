#!/usr/bin/env bash

cd "$(dirname "$0")"
cd ..

PHP_CLI_SERVER_WORKERS=4 php -S 0.0.0.0:8080 -t ./public/ ./server.php
