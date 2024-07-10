#!/usr/bin/env bash

# stop all sync containers
# shellcheck disable=SC2046
docker stop $(docker ps -a -q --filter "status=running" --filter "name=sync")

# start sync containers for this project
cd ..
docker-sync start
# shellcheck disable=SC2164
cd ./docker

# start project
docker-compose up -d --remove-orphans --build