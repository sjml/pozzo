#!/usr/bin/env bash

cd "$(dirname "$0")"

# will always be running against localhost (because want to make sure)
#   the API is always working) but host might have different names for itself
SERVER="localhost:8080"
PHP=php
FULL=0
ONLY_RESET=0

# <sigh> getopts doesn't do long arguments
args=($@)
for a in ${args[@]}; do
  IFS='=' read -ra arg <<< $a
  case ${arg[0]} in
    "--server")
      SERVER=${arg[1]}
      ;;
    "--php")
      PHP=${arg[1]}
      ;;
    "--full")
      FULL=${arg[1]}
      ;;
    "--only-reset")
      ONLY_RESET=1
      ;;
  esac
done

CURL="curl -s -i"

# reset everything
jwt=$($PHP ./get_test_key.php)
$CURL \
  -H "Authorization: Bearer $jwt" \
  $SERVER/api/reset
if [[ $? -ne 0 ]]; then
  echo "Could not connect to $SERVER"
  exit 1
fi
echo

# make a new user on the fresh site
jwt=$($PHP ./make_test_user.php)

if [[ $ONLY_RESET = 1 ]]; then
  exit
fi

# upload some images
pushd ../samples > /dev/null
if [[ $FULL -eq 1 ]]; then
  imgs=$(ls)
else
  imgs=$(ls | shuf -n 5)
fi
popd > /dev/null
for i in ${imgs[*]}; do
  $CURL \
    -H "Authorization: Bearer $jwt" \
    -F "photoUp=@../samples/$i" \
    $SERVER/api/upload
  echo
done

# make a new private album
$CURL \
  -H "Authorization: Bearer $jwt" \
  -X POST --data '{"title": "testAlbum", "isPrivate": 1}' \
  $SERVER/api/album/new
echo

# put some of the images in it
for i in $(seq 2); do
  $CURL \
    -H "Authorization: Bearer $jwt" \
    -X POST --data "{\"photoID\": $i, \"albumID\": 2}" \
    $SERVER/api/photo/copy
  echo
done
