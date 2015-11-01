#!/usr/bin/env bash

WORKDIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

if [ -f "$WORKDIR/../mysql/backups/$1" ]; then
    docker exec -it myassistant_database_1 /bin/bash -c "gunzip < /tmp/backups/$1 | mysql site"
    echo "Database restored with: $1 dump"
else
    echo "File should exist under mysql/backups folder. Usage: restore_db.sh filename.sql.gz";
fi