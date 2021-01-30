#!/usr/bin/env bash

cd "$(dirname "$0")"
cd ..

prettier \
  --config .prettierrc \
  --parser php \
  --write \
  '**/**.php'
