<?php
header ('Content-type=application/json');

require 'mpm_server.creds';

$body = file_get_contents("php://input");
$object = json_decode($body, true);

$dbconn = pg_connect ("host=$pgdb_host port=$pgdb_port dbname=$pgdb_dbname user=$pgdb_user password=$pgdb_password");
$dbresult_authquery = pg_query_params ($dbconn, 'SELECT user, salt, hash FROM tbl_user WHERE user=$1', array ($object['MPM_USER']));
// TODO: Stopped here last
// 1. Rehash the given password, compare to $result[hash] and end imm if not matching
// 2. Insert the single metrics record into tbl_metrics
// 3. Insert the multiple ifmetrics record into tbl_ifmetrics
// 4. Insert the multiple diskmetrics record into tbl_diskmetrics

print_r ($object);

?>
