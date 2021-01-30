#!/usr/bin/env bash

cd "$(dirname "$0")"
cd ..

php -S 0.0.0.0:8080 -t ./public/
