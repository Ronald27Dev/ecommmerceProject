<?php

namespace Ronald\DB;

use PDO;
use PDOException;

class Sql extends PDO {

    const HOSTNAME  = "127.0.0.1";
    const DBNAME    = "ecommerce";
    const USERNAME  = "ronald";
    const PASSWORD  = "doctum2023";

    public function __construct() {
        try {
            parent::__construct(
                "mysql:host=".self::HOSTNAME.";dbname=".self::DBNAME,
                self::USERNAME,
                self::PASSWORD
            );
            
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            exit();
        }
    }

    private function setParams($statement, $parameters = array()) {
        foreach($parameters as $key => $value) {
            $this->bindParam($statement, $key, $value);
        }
    }

    private function bindParam($statement, $key, $value) {
        $statement->bindParam($key, $value);
    }

    public function queryE($rawQuery, $params = array()) {
        try {
            $stmt = $this->prepare($rawQuery);
            $this->setParams($stmt, $params);
            $stmt->execute();
        } catch (PDOException $e) {
            echo "Query failed: " . $e->getMessage();
            exit();
        }
    }

    public function select($rawQuery, $params = array()): array {
        try {
            $stmt = $this->prepare($rawQuery);
            $this->setParams($stmt, $params);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Query failed: " . $e->getMessage();
            return [];
        }
    }
}
?>