<?php

header ('Content-type=application/json');

require_once 'mpm_server.creds';
require_once 'util.php';


$body = file_get_contents("php://input");
$object = json_decode($body, true);
$result = -1;
try {
   $result = util_agent_store_metric ($object);
} catch (Exception|TypeError $ex) {
   error_log ($ex->getMessage ());
   error_log ($body);
}

echo '{ "result:", ' . print_r ($result, true) . ' }';

?>
