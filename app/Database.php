<?php
namespace App;

use PDO;
use function GuzzleHttp\Psr7\str;

class Database
{
    private ?PDO $pdo;
    private static ?\App\Database $instance = null;
    public const TABLE_RECOGNICTION_SEPEATOR = "____"; //this string is not allowed to apear in table field names
    public const ID = "id";

    private function __construct()
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

    /*
     *  Singelton methods
     */

    private static function getInstance(): ?Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    private static function getPDO(): ?PDO
    {
        return static::getInstance()->pdo;
    }

    /*
     *  publics
     */

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

    public static function fetchSelect(
        string $table,
        array $filter,
        array $fields,
        array $foreigns,
        array $manyToManys,
        int $limit,
        bool $all = true
    )
    {
        $fields = static::AlterElements($fields, function ($element) use ($table) {
            return "$table.$element as $table" . self::TABLE_RECOGNICTION_SEPEATOR . $element;
        });
        $selectedFields = self::mergeArrayValues(
            $fields,
            self::toStringsFieldObjects($foreigns),
            self::toStringsFieldObjects($manyToManys)
        );
        $query = "SELECT " . self::arrayToString($selectedFields) . " FROM $table";
        $query .= self::getForeignJoin($foreigns, $table);
        $query .= self::getManyToManyJoin($manyToManys, $table);
        $values = [];
        if(sizeof($filter) > 0) {
            $query .= " WHERE ";
            foreach ($filter as $table => $fields) {
                foreach ($fields as $key => $value) {
                    $query .= "$table.$key = ? AND ";
                    array_push($values, $value);
                }
            }
            $query = self::shaveOffEnd($query, 4);
        }
        $query .= " LIMIT $limit";
        return self::fetchWithBoundParams($query, $values, $all);
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

    /*
     *  quick (no security)
     */

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

    /*
     *  helper methods (private)
     */
    //fetch model method
    private static function toStringsFieldObjects($objectArray): array
    {
        $stringArray = [];
        foreach ($objectArray as $foreignTable => $object) {
            foreach ($object["fields"] as $field) {
                $value = "$foreignTable.$field as $foreignTable" . self::TABLE_RECOGNICTION_SEPEATOR . "$field"; // "__" is to be able to differentiate where the value came from later on
                array_push($stringArray, $value);
            }
        }
        return $stringArray;
    }
    //fetch model method
    private static function getForeignJoin($foreigns, $table): string
    {
        $string = "";
        foreach ($foreigns as $foreignTable => $object) {
            $string .= " JOIN $foreignTable ON $table." .
                $object["onKey"] . " = $foreignTable." .
                $object["foreignKey"];
        }
        return $string;
    }
    //fetch model method
    private static function getManyToManyJoin($manyToManys, $table): string
    {
        $string = "";
        foreach ($manyToManys as $foreignTable => $object) {
            $singularForeign = self::shaveOffEnd($foreignTable, 1);
            $singular = self::shaveOffEnd($table, 1);

            $string .= " LEFT JOIN " . $object["linkingTable"] . " ON " .
                "$table.id = " . $object["linkingTable"] . "." .  $singular . "_id " .
                "LEFT JOIN " . $foreignTable. " ON " .
                "$foreignTable.id = " . $object["linkingTable"] . "." . $singularForeign . "_id";
        }
        return $string;
    }

    private static function AlterElements($array, $function): array {
        $newArray = [];
        foreach ($array as $element) {
            array_push($newArray, $function($element));
        }
        return $newArray;
    }
    //["element1", "element2", "element3"] to "element1, element2, elmenent3"
    private static function arrayToString($array, $prefix = "", $suffix = "")
    {
        $string = "";
        foreach ($array as $value) {
            $string = "$string$prefix$value$suffix, ";
        }
        $string = substr($string, 0, strlen($string) - 2); //remove last ", "
        return $string;
    }

    private static function shaveOffEnd(string $string, int $num):string {
        return substr($string, 0, strlen($string) - $num);
    }

    private static function mergeArrayValues(...$arrays): array
    { //creates array with the values of the arrays but doesnt care about keys
        $merged = [];
        foreach ($arrays as $array) {
            foreach ($array as $value) {
                array_push($merged, $value);
            }
        }
        return $merged;
    }

    private static function toQuestionMarks($array): string {
        $string = "";
        for($i = 0; $i < sizeof($array); $i++) {
            $string = "$string?, ";
        }
        $string = substr($string, 0, strlen($string) - 2); //remove last ", "
        return $string;
    }
}