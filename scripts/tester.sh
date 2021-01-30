#!/usr/bin/env bash

cd "$(dirname "$0")"

# reset everything
jwt=$(php ./get_test_key.php)
curl \
  -H "Authorization: Bearer $jwt" \
  localhost:8080/api/reset
echo

# make a new user on the fresh site
jwt=$(php ./make_test_user.php)

# upload some images
imgs=(IMG_3845.jpeg IMG_3957.jpeg IMG_3955.jpeg)
for i in ${imgs[*]}; do
  curl \
    -H "Authorization: Bearer $jwt" \
    -F "photoUp=@../samples/$i" \
    localhost:8080/api/upload
  echo
done

# make a new album
curl \
  -H "Authorization: Bearer $jwt" \
  -X POST --data '{"title": "testAlbum"}' \
  localhost:8080/api/album/new
echo

# put some of the images in it
for i in $(seq 2); do
  curl \
    -H "Authorization: Bearer $jwt" \
    -X POST --data "{\"photoID\": $i, \"albumID\": 2}" \
    localhost:8080/api/photo/copy
  echo
done
