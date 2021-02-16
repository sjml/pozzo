#!/usr/bin/env bash

cd "$(dirname $0)"

if [ ! -f ./phpcov.phar ]; then
  wget https://phar.phpunit.de/phpcov.phar
fi

php phpcov.phar merge --html ./output/html/ ./raw_coverage/
php phpcov.phar merge --clover ./output/coverage.xml ./raw_coverage/
