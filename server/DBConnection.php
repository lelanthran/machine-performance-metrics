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

   public static function querySucceeded ($query_results) :bool {
      if (   is_array ($query_results) === true
          && is_array ($query_results[0]) === true
          && $query_results[0][0] !== false) {
          return true;
      }
      return false;
   }

   public function query (string $query, array $params) :array {
      $name = util_randstring (25);
      $deallocate = "DEALLOCATE \"$name\";";

      pg_prepare ($this->dbhandle, $name, $query);
      $pgresult = pg_execute ($this->dbhandle, $name, $params);

      if ($pgresult === false) {
         return array (array (false), array (false));
      }

      $headers = array ();
      $nfields = pg_num_fields ($pgresult);
      $nrows = pg_num_rows ($pgresult);

      for ($i=0; $i<$nfields; $i++) {
         array_push ($headers, pg_field_name ($pgresult, $i));
      }

      $matrix = array ($headers);
      for ($i=0; $i<$nrows; $i++) {
         array_push ($matrix, pg_fetch_row ($pgresult, $i));
      }

      pg_free_result ($pgresult);
      pg_send_execute ($this->dbhandle, $deallocate, array ());
      return $matrix;
   }
}

?>
