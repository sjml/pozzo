#!/usr/bin/env bash

curl localhost:8080/api/reset
curl -F "photoUp=@./samples/IMG_3845.jpeg" --output - localhost:8080/api/upload
curl -F "photoUp=@./samples/IMG_3957.jpeg" --output - localhost:8080/api/upload
curl -F "photoUp=@./samples/IMG_3955.jpeg" --output - localhost:8080/api/upload
