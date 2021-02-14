#!/usr/bin/env bash

cd "$(dirname "$0")"
cd ..
ROOT_DIR=$(pwd)

rm -rf $ROOT_DIR/dist
mkdir $ROOT_DIR/dist

copies=(app lib scripts LICENSE README.md)
for item in ${copies[@]}; do
  cp -R $item $ROOT_DIR/dist
done

mkdir $ROOT_DIR/dist/public
pushd $ROOT_DIR/public > /dev/null
  copies=(*)
  for item in ${copies[@]}; do
    if [[ $item = "photos" ]]; then
      continue
    fi
    cp -R $item $ROOT_DIR/dist/public
  done
  cp .htaccess $ROOT_DIR/dist/public
popd > /dev/null

# probably a better way to cache-bust, but here we are
pushd $ROOT_DIR/dist/public > /dev/null
  busts=(/css/pozzo-global.css /build/bundle.css /build/bundle.js)
  for b in ${busts[@]}; do
    hash=$(md5 -q .$b)
    sed -i '' -e "s:$b:$b?$hash:g" "./index.html"
  done
popd > /dev/null
