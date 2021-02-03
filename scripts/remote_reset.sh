#!/usr/bin/env bash

# Running the reset over the internet is super slow since I'm currently on
#   the other side of the planet from my hosting server. So SSH into the server
#   and do it there instead, where the data never even has to leave the colo.

HOST=pozzo.shaneliesegang.com
PHP=php-7.4

echo "SSHing to remote host and running reset there..."
ssh napthali@shaneliesegang.com \
  "cd $HOST; ./scripts/tester.sh --full=1 --server=https://$HOST --php=$PHP"
