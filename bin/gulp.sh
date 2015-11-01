#!/usr/bin/env bash

docker exec -it myassistant_nodejs_1 /bin/bash -c "npm install && bower install --allow-root --config.interactive=false && gulp"