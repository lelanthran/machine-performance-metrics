#!/bin/bash

export SCRIPT="
CREATE ROLE XXXX WITH LOGIN PASSWORD 'YYYY' NOSUPERUSER INHERIT NOCREATEDB NOCREATEROLE NOREPLICATION;\
GRANT CONNECT ON DATABASE ZZZZ TO XXXX;\
GRANT USAGE ON SCHEMA public TO XXXX;\
GRANT SELECT ON ALL TABLES IN SCHEMA public TO XXXX;\
GRANT SELECT ON ALL SEQUENCES IN SCHEMA public TO XXXX;\
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

echo $SCRIPT | sed "s/XXXX/$1/g; s/YYYY/$2/g; s/ZZZZ/$3/g" | psql
