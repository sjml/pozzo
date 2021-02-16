#!/usr/bin/env bash

set -e

cd "$(dirname "$0")"
cd ..
ROOT_DIR=$(pwd)

rm -rf $ROOT_DIR/dist
mkdir $ROOT_DIR/dist

copies=(app scripts LICENSE README.md)
for item in ${copies[@]}; do
  cp -R $item $ROOT_DIR/dist
done

cp $ROOT_DIR/composer.json $ROOT_DIR/dist
cp $ROOT_DIR/composer.lock $ROOT_DIR/dist
pushd $ROOT_DIR/dist > /dev/null
  if [ ! -z "$POZZO_CODE_COVERAGE" ]; then
    composer install
  else
    composer install --no-dev
  fi
popd > /dev/null
rm $ROOT_DIR/dist/composer.json $ROOT_DIR/dist/composer.lock

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
  rm build/bundle.js.map
  busts=(/css/pozzo-global.css /build/bundle.css /build/bundle.js)
  for b in ${busts[@]}; do
    hash=$(md5sum .$b | awk '{print $1}')
    newname=${b%.*}.$hash.${b##*.}
    mv .$b .$newname
    sed -i.bak -e "s:$b:$newname:g" "./index.html"
    rm ./index.html.bak
  done
popd > /dev/null
