<?php
class Connection {
    private $servername = "localhost:3307"; 
    private $username = "root"; 
    private $password = ""; 
    private $dbname = "wovens"; 
    public $conn;

    public function __construct() {
        $this->connect();
    }

    // Create connection using PDO
    public function connect() {
        try {
            $this->conn = new PDO("mysql:host=$this->servername;dbname=$this->dbname", $this->username, $this->password);
            // Set the PDO error mode to exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>
