#!/bin/bash

export SCRIPT="
CREATE USER XXXX WITH PASSWORD 'YYYY';
"

function die () {
   echo $1
   echo '
Usage: pg_rouser <username> <password> <database>

Create a readonly user for the specified database using the specified
username and password.

'
   exit -1;
}


[ -z "$1" ] && die "Missing username to create"
[ -z "$2" ] && die "Missing password for new user"
[ -z "$3" ] && die "Missing database to grant rights on"

echo "
CREATE ROLE readonly;
GRANT CONNECT ON DATABASE $3 TO readonly;
GRANT USAGE ON SCHEMA public TO readonly;
GRANT SELECT ON ALL TABLES IN SCHEMA public TO readonly;
ALTER PRIVILEGES IN SCHEMA public GRANT SELECT ON TABLES TO readonly;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT ON TABLES TO readonly;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO $1;
" | psql $3

echo $SCRIPT | sed "s/XXXX/$1/g; s/YYYY/$2/g; s/ZZZZ/$3/g" | psql

echo "GRANT readonly TO $1;" | psql

