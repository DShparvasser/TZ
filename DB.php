<?php

class DB
{
    private static $instances;
    private $dbConn;

    protected function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instances == null) {
            $clasName = __CLASS__;
            self::$instances = new $clasName;
        }

        return self::$instances;
    }


    public static function initConnection()
    {
        $db = self::getInstance();
        $db->dbConn = new mysqli('localhost', 'root', 'root', 'integrations');

        return $db;
    }

    public static function getDbConn()
    {
        try {
            $db = self::initConnection();

            return $db->dbConn;
        } catch (\Exception $ex) {
            echo "I was unable to open a connection to the database. " . $ex->getMessage();

            return null;
        }
    }

    public function insert($table, $para = [])
    {
        $conn = $this->dbConn;

        $table_columns = implode(',', array_keys($para));
        $table_value = implode("','", $para);

        $sql = "INSERT INTO $table($table_columns) VALUES('$table_value')";

        return $conn->query($sql);
    }

    public function update($table, $para = [], $where)
    {
        $conn = $this->dbConn;

        $args = [];

        foreach ($para as $key => $value) {
            $args[] = "$key = '$value'";
        }

        $sql = "UPDATE  $table SET " . implode(',', $args);

        $sql .= " WHERE $where";

        return $conn->query($sql);
    }

    public function select($table, $rows = '*', $where = null)
    {
        $conn = $this->dbConn;

        if ($where != null) {
            $sql = "SELECT $rows FROM $table" . " WHERE $where";
        } else {
            $sql = "SELECT $rows FROM $table";
        }

        return $conn->query($sql);
    }
}