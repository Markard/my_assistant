#!/usr/bin/env bash

DUMP_FILE="$(date +%Y-%m-%d-%H.%M.%S).sql.gz"
docker exec -it myassistant_database_1 /bin/bash -c "mysqldump site | gzip -9 > /tmp/backups/$DUMP_FILE"

echo "Dump: $DUMP_FILE created"