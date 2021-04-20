<?php
namespace App;

use PDO;
use function GuzzleHttp\Psr7\str;

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

    public static function fetchWithJoinAndFilter(
        $table,
        $foreignTable,
        $fields,
        $foreignFields,
        $key,
        $foreignKey,
        $filterArray,
        $all = true ,
        $joinMethod = "INNER JOIN"
    )
    {
        //todo: refactor this thing
        $columnsString = "";
        foreach ($fields as $field) {
            $columnsString = "$columnsString$table.$field as $table". "__" .  "$field, ";
        }
        foreach ($foreignFields as $field) {
            $columnsString = "$columnsString$foreignTable.$field as $foreignTable". "__" .  "$field, ";
        }

        $columnsString = substr($columnsString, 0, strlen($columnsString) - 2); //remove last ", "
        $query ="SELECT " . $columnsString .
            " FROM $table $joinMethod $foreignTable ON $foreignTable.$key = $table.$foreignKey";
        return self::fetchQueryWithFilter($query, $filterArray, $all);
    }

    public static function fetchWithFilter(
        string $table,
        array $filterArray = [],
        array $fields = [],
        bool $all = true
    )
    {
        $columnString = self::arrayToString($fields);
        $query = "SELECT $columnString FROM $table";
        return self::fetchQueryWithFilter($query, $filterArray, $all);
    }

    public static function fetchQueryWithFilter($query, $filterArray, $all) {
        $query = "$query WHERE";
        foreach ($filterArray as $field => $value) {
            $query = $query . " $field = ? AND";
        }
        $query = substr($query, 0, strlen($query) - 4); //remove last " AND"
        $stmt = static::getPDO()->prepare($query);
        return self::fetchWithBoundParams($query, $filterArray, $all);
    }

    public static function update($table, $values, $id, $serial="id") {
        $query = "UPDATE $table SET " .
            self::arrayToString(array_keys($values), "", " = ?") .
            " WHERE $serial = ?";
        $values[$serial] = $id;
        self::executeWithBoundParams($query,  $values);
    }

    public static function insert($table, $values, $getId = false): ?int
    {
        $keys = array_keys($values);
        $queryString = "INSERT INTO $table (" . self::arrayToString($keys) .") VALUES (". self::toQuestionMarks($values) . ")";
        self::executeWithBoundParams($queryString, $values);
        if ($getId) {
            return (int)self::getPDO()->lastInsertId();
        }
        return null;
    }

    private static function toQuestionMarks($array): string {
        $string = "";
        for($i = 0; $i < sizeof($array); $i++) {
            $string = "$string?, ";
        }
        $string = substr($string, 0, strlen($string) - 2); //remove last ", "
        return $string;
    }
    //["element1", "element2", "element3"] to "element1, element2, elmenent3"
    public static function arrayToString($array, $prefix = "", $suffix = "")
    {
        $string = "";
        foreach ($array as $value) {
            $string = "$string$prefix$value$suffix, ";
        }
        $string = substr($string, 0, strlen($string) - 2); //remove last ", "
        return $string;
    }

    public static function fetchWithBoundParams($query, $params, $all = true)
    {
        $stmt = self::executeWithBoundParams($query, $params);
        if ($all) {
            return $stmt->fetchAll();
        }
        return $stmt->fetch();
    }

    public static function executeWithBoundParams($query, $params) {
        $stmt = static::getPDO()->prepare($query);
        $index = 1;

        foreach($params as $key => $_) {
            $stmt->bindParam($index,$params[$key]);
            $index = $index + 1;
        }
        $stmt->execute();
        return $stmt;
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