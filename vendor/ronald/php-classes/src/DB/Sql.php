<?php

namespace Ronald\DB;

class Sql{

    const HOSTNAME  = "127.0.0.1";
    const DBNAME    = "ecommerce";
    const USERNAME  = "root";
    const PASSWORD  = "doctum2023";

    private $conn;

    public function __construct(){
        
        try {
            $this->conn = new \PDO(
                "mysql:host=".self::HOSTNAME.";dbname=".self::DBNAME,
                self::USERNAME,
                self::PASSWORD
            );
            
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            
            echo "Conexão Falhou: " . $e->getMessage();
            exit();
        }
    }
    
    private function setParams($statement, $parameters = array()){

        foreach($parameters as $key => $value){

            $this->bindParam($statement, $key, $value);
        }
    }

    private function bindParam($statement, $key, $value){

        $statement->bindParam($key, $value);
    }

    public function query($rawQuery, $params = array()){

        try {
            $stmt = $this->conn->prepare($rawQuery);

            $this->setParams($stmt, $params);

            $stmt->execute();
        } catch (\PDOException $e) {
            
            echo "Query falhou: " . $e->getMessage();
            exit(); 
        }
    }

    public function select($rawQuery, $params = array()):array
    {
        try {
            $stmt = $this->conn->prepare($rawQuery);

            $this->setParams($stmt, $params);

            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            
            echo "Query falhou: " . $e->getMessage();
            return [];
        }
    }
}
?>
