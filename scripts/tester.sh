#!/usr/bin/env bash

cd "$(dirname "$0")"

server="localhost:8080"

# reset everything
jwt=$(php ./get_test_key.php)
curl \
  -H "Authorization: Bearer $jwt" \
  $server/api/reset
echo

# make a new user on the fresh site
jwt=$(php ./make_test_user.php)

# upload some images
pushd ../samples > /dev/null
if [[ $1 = "full" ]]; then
  imgs=$(ls)
else
  imgs=$(ls | shuf -n 5)
fi
popd > /dev/null
for i in ${imgs[*]}; do
  curl \
    -H "Authorization: Bearer $jwt" \
    -F "photoUp=@../samples/$i" \
    $server/api/upload
  echo
done

# make a new album
curl \
  -H "Authorization: Bearer $jwt" \
  -X POST --data '{"title": "testAlbum"}' \
  $server/api/album/new
echo

# put some of the images in it
for i in $(seq 2); do
  curl \
    -H "Authorization: Bearer $jwt" \
    -X POST --data "{\"photoID\": $i, \"albumID\": 2}" \
    $server/api/photo/copy
  echo
done
