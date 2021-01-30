#!/usr/bin/env bash

cd "$(dirname "$0")"
cd ..

prettier --config .prettierrc --write --parser php '**/**.php'
