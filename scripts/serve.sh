#!/usr/bin/env bash

cd "$(dirname "$0")"
cd ../frontend

if [[ $1 = "debug" ]]; then
  npm run dev -- debug
else
  npm run dev
fi
