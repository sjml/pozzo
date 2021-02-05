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
pushd ./public > /dev/null
  copies=(*)
  for item in ${copies[@]}; do
    if [[ $item = "img" ]]; then
      continue
    fi
    cp -R $item $ROOT_DIR/dist/public
  done
popd > /dev/null

cp $ROOT_DIR/scripts/configs/remote.htaccess $ROOT_DIR/dist/public/.htaccess
