<?php

namespace App\Models;

use App\Database;

/*
 * assumes serial = "id"
 */
class Model
{
    private array $setFields = []; // fields that have been set by php

    private array $tableFields = []; // fields that come from table
    private array $foreignFields = []; // fields that come from foreing tables
    private array $manyToManyFields = []; //list of fields from many to many tables

    private array $requiredFields = [];
    private array $requiredForeginTables = [];
    private array $requiredManyToManyTables = [];

    private int $id;
    public const TABLE = "";

    public function __construct($id, $tableFields = [])
    {
        $this->id = $id;
        $this->tableFields = $tableFields;
    }
    /*
     * factories
     */

    //returns instance of which ever class called it (return type static)
    public static function getWhere($fields, $serial="id")//:?static
    {
        $result = Database::fetchWithFilter(static::TABLE,
            $fields,
            [$serial],
            false);
        if($result) {
            return new static($result[$serial], $fields);
        }
        return null;
    }
    //    -//-   -||-
    public static function insert($fields)
    {
        $id = Database::insert(static::TABLE, $fields, true);
        return new static($id, $fields);
    }

    /*
     * getters
     */

    public function getField($field): ?string {
        return $this->setField[$field] ?? $this->tableFields[$field] ?? null;
    }

    private function getAllFields(): array
    {
        return array_merge($this->tableFields, $this->setFields);
    }

    private function getMissingFields($requiredFields): array
    {
        return $this->getMissingKeysFromArray($this->getAllFields(), $requiredFields);
    }

    private function getMissingKeysFromArray($array, $keys): array
    {
        $missingKeys = [];
        foreach($keys as $key) {
            if (!in_array($key, array_keys($array))) {
                array_push($missingKeys, $key);
            }
        }
        return $missingKeys;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /*
     * setters
     */

    public function setFields($newFields) {
        foreach ($newFields as $field => $value) {
            $this->setField($field, $value);
        }
    }

    public function setField($key, $value) {
        $tableValue = $this->tableFields[$key] ?? null;
        $setValue = $this->setFields[$key] ?? null;

        if($setValue and $tableValue) {
            if($setValue == $tableValue) {
                unset($this->setFields[$key]);
                return;
            }
        }
        $this->setFields[$key] = $value;
    }

    private function setForeignField($table, $field, $value) {
        $this->foreignFields[$table][$field] = $value;
    }

    /*
     *  requires
     */

    protected function requireFields($fields) {
        $this->requiredFields = $fields;
    }

    protected function requireForeignFields($table, $fields, $foreignKey="id", $onThisKey="id") {
        $this->requiredForeginTables[$table] = [
            "foreignKey" => $foreignKey,
            "onThisKey" => $onThisKey,
            "fields" => $fields
        ];
    }

    protected function requireManyToManyFields($table, $linkingTable, $fields, $key = "id") {
        $this->requiredManyToManyTables[$table] = [
            "key" => $key,
            "linkingTable" => $linkingTable,
            "fields" => $fields
        ];
    }
    //DOESNT PREVENT OVERFETCHING (THIS METHOD SHOULD ONLY BE RUN ONCE (per model) XD)
    protected function fetch()
    {
        $data = Database::fetchModel(static::TABLE, $this->getId(),
            $this->requiredFields,
            $this->requiredForeginTables,
            $this->requiredManyToManyTables
        );
        //IF THERE WERE MANY TO MANY TABLES -> ARRAY OF ASSOCIATIVE ARRAYS
        //OTHERWISE -> ONE ASSOCIATIVE ARRAY ONLY
        foreach ($data as $array) {
            $manyToManyFields = [];
            foreach ($array as $field => $value) {
                [$field, $table] = explode(Database::TABLE_RECOGNICTION_SEPEATOR, $field, 2);
                if ($table === static::TABLE) { //no table -> this table (non-foreign)
                    $this->tableFields[$field] = $value;
                }
                else if ($this->requiredForeginTables[$table] ?? false) { // none many to many
                    $this->setForeignField($table, $field, $value);
                }
                else {

                }
            }
        }
        var_dump($this->tableFields);
        var_dump($this->foreignFields);
        var_dump($this->manyToManyFields);
    }

    public function save()
    {
        Database::update(static::TABLE, $this->setFields, $this->id);
    }
}