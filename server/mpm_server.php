<?php

header ('Content-type=application/json');

require_once 'mpm_server.creds';
require_once 'util.php';


$body = file_get_contents("php://input");
$object = json_decode($body, true);
$result = util_agent_store_metric ($object);

echo '{ "result:", ' . print_r ($result, true) . ' }';

?>
