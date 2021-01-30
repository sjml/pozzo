#!/usr/bin/env bash

cd "$(dirname "$0")"
cd ..

SAMPLES_DIR=samples
BASE_URL=localhost:8080/api
# BASE_URL=http://pozzo.shaneliesegang.com/api


curl $BASE_URL/reset

files=$SAMPLES_DIR/*

parallel -j6 curl -s -F "photoUp=@./{}" --output - "$BASE_URL/upload" ::: ${files[*]}
