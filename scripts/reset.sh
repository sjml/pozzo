#!/usr/bin/env bash

cd "$(dirname "$0")"
cd ..

rm -f pozzo.DB
rm -f pozzo.DB-wal
rm -f pozzo.DB-shm
rm -rf public/photos
