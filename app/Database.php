<?php
namespace App;

use PDO;

class Database
{
    private ?PDO $pdo;
    private static ?\App\Database $instance = null;

    protected function __construct()
    {
        $db = parse_url(getenv("DATABASE_URL"));
        $pdo = null;
        if ($_SERVER['HTTP_HOST'] == "pog-fish.test") {
            $dsn = "pgsql:host=ec2-54-155-208-5.eu-west-1.compute.amazonaws.com;
            port=5432;
            dbname=d50e4u4gb8apae;
            user=zarymkhctkbfrv;
            password=c48fc77df719128971e3ad0417fef280c8030be2dce085bc1d3bf5fe8457d7a0";
            $pdo = new PDO($dsn);
        }
        else {
            $pdo = new PDO("pgsql:" . sprintf(
                "host=%s;port=%s;user=%s;password=%s;dbname=%s",
                $db["host"],
                $db["port"],
                $db["user"],
                $db["pass"],
                ltrim($db["path"], "/")
            ));
        }
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    //todo: refactor this giant function
    public static function fetchWithFilter(
        string $table,
        array $filterArray = [],
        array $fields = [],
        bool $all = true
    )
    {
        $columnString = self::arrayToString($fields);
        $query = "SELECT $columnString FROM $table WHERE";
        foreach ($filterArray as $field => $value) {
            $query = $query . " $field = :$field AND";
        }
        $query = substr($query, 0, strlen($query) - 4); //remove last " AND"
        $stmt = static::getPDO()->prepare($query);
        $params = self::addPrefixToKeys($filterArray, ":");
        return self::fetchWithBoundParams($query, $params, $all);
    }

    public static function insert($table, $values)
    {
        $keys = array_keys($table);
        $queryString = "INSERT INTO $table (" . self::arrayToString($keys) .") VALUES (".
            self::arrayToString($keys, ":").")";
        $params = self::addPrefixToKeys($values, ":");
        self::executeWithBoundParams($queryString, $params);
    }

    public static function addPrefixToKeys($array, $prefix)
    {
        foreach ($array as $key => $value) {
            $array["$prefix$key"] = $value;
        }
        return $array;
    }
    //["element1", "element2", "element3"] to "element1, element2, elmenent3"
    public static function arrayToString($array, $prefix = "") {
        $string = "";
        foreach ($array as $value) {
            $string = "$string$prefix$value, ";
        }
        $string = substr($string, 0, strlen($string) - 2); //remove last ", "
        return $string;
    }

    public static function executeWithBoundParams($query, $params) {
        $stmt = static::getPDO()->prepare($query);
        foreach($params as $bind => $value) {
            $stmt->bindParam($bind,$params[$bind]);
        }
        $stmt->execute();
        return $stmt;
    }

    public static function fetchWithBoundParams($query, $params, $all = true)
    {
        $stmt = self::executeWithBoundParams($query, $params);
        if ($all) {
            return $stmt->fetchAll();
        }
        return $stmt->fetch();
    }

    public static function getInstance(): ?Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public static function getPDO(): ?PDO
    {
        return static::getInstance()->pdo;
    }

    public static function quickExecute($query)
    {
        $stmt = static::getPDO()->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public static function quickFetch($query): array
    {
        $stmt = self::quickExecute($query);
        return $stmt->fetchAll();
    }
}