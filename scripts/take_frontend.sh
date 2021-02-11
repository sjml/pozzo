#!/usr/bin/env bash

cd "$(dirname "$0")"

cd ../frontend
npm run build
cd ..
rm -rf ./public/build
cp -R ./frontend/public/* ./public
