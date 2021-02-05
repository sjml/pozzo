#!/usr/bin/env bash

cd "$(dirname "$0")"
cd ../frontend

npm run dev -- $@
