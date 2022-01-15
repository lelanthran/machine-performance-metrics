<?php

declare (strict_types=1);

class DBConnection {

   // TODO: Store the connection string and only make a connection when a
   // query is made, otherwise we are using two database handles when only one
   // is needed
   private $dbhandle = null;

   public function __construct (string $host, int $port, string $dbname,
                                string $user, string $password) {
      $this->open ($host, $port, $dbname, $user, $password);
   }

   public function open (string $host, int $port, string $dbname,
                         string $user, string $password) :void {
      $this->close ();
      $rc = $this->dbhandle = pg_connect ("host=$host port=$port dbname=$dbname"
                                        . " user=$user password=$password");
      if ($rc === false) {
         error_log ("Failed to open database [$host, $port, $dbname, $user]");
      }
   }

   public function close () :void {
      if ($this->dbhandle!==null) {
         pg_close ($this->dbhandle);
      }
   }
}

?>
