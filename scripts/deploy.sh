#!/usr/bin/env bash

rsync -zvhr --exclude="*/.DS_Store" ./dist/ napthali@shaneliesegang.com:/home/napthali/pozzo.shaneliesegang.com
