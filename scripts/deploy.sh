#!/bin/sh
set -e

cd /opt/kialent

git pull --ff-only

docker compose -f compose.yml -f compose.prod.yml up -d --build

docker image prune -f

echo "deploy finished: $(git log --oneline -1)"