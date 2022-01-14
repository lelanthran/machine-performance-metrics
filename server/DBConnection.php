<?

class DBConnection {

   private $host = '';
   private $port = 0;
   private $dbname = '';
   private $user = '';
   private $password = '';

   public function __construct ($host, $port, $dbname, $user, $password) {
      this->$host     = $host;
      this->$port     = $port;
      this->$dbname   = $bname;
      this->$user     = $user;
      this->$password = $password;
   }

}

?>
