#!/usr/bin/env bash

cd ..
docker-sync stop
# shellcheck disable=SC2164
cd ./docker

docker-compose down