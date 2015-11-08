<?php
class databaseManager {
	
	private $host;
	private $port;
	private $username;
	private $userpass;
	private $dbname;
	
	private $dbconn;
	
	public function __construct($host,$port,$username,$userpass,$dbname) {
        $this->host = $host;
		$this->port = $port;
		$this->username = $username;
		$this->userpass = $userpass;
		$this->dbname = $dbname;
    }
	
	public function connect () {
		//if !(isset ($this->host)) or !(isset ($this->port)) or !(isset ($this->username)) or !(isset ($this->userpass)) or !(isset ($this->dbname)) {
		//	return 0;
		//}
		
		$this->dbconn = pg_connect("host=$this->host dbname=$this->dbname user=$this->username password=$this->userpass");
			//or return 0;
	}
    
    public function doQuery ($queryStr) {
        $result = pg_query($queryStr);
        return $result;
    }
    
    public function closeConnection () {
        pg_close($this->dbconn);
    }
}

?>