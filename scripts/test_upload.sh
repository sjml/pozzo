#!/usr/bin/env bash

cd "$(dirname "$0")"
cd ..

SAMPLES_DIR=samples
BASE_URL=localhost:8080/api
# BASE_URL=https://pozzo.shaneliesegang.com/api


curl $BASE_URL/reset

files=$SAMPLES_DIR/*

parallel -j4 curl -s -F "photoUp=@./{}" --output - "$BASE_URL/upload" ::: ${files[*]}
