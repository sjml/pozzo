#!/usr/bin/env bash

cd "$(dirname "$0")"

cd ../frontend
npm run build
cd ..
rm -rf ./public/build
rm -rf ./public/img
cp -R ./frontend/public/* ./public
