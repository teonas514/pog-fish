<?php

namespace App\Models;

use App\Database;

/*
 * assumes serial = "id"
 */
class Model
{
    public array $fields = [];
    public array $foreignFields = []; //
    public int $id;
    public const TABLE = "";

    public function __construct($id)
    {
        $this->id = $id;
        $this->fields = [];
    }
    //returns instance of which ever class called it (return type static)
    public static function getWhere($fields, $serial="id")//: ?static
    {
        $result = Database::fetchWithFilter(static::TABLE,
            $fields,
            [$serial],
            false);
        if($result) {
            $instance = new static($result[$serial]);
            $instance->fields = $fields;
            return $instance;
        }
        return null;
    }

    public static function insert($fields) {
        Database::insert(static::TABLE, $fields);
    }

    public static function getAll($requiredFields) {

    }

    public function getFields(...$fields): ?array {
        if(!$this->requireFields($fields)) {
            return null;//couldnt require those fields
        }
        $return = [];
        foreach($fields as $field) {
            $return[$field] = $this->fields[$field];
        }
        return $return;
    }

    public function getFieldsWithJoin($fields, $foreginFields, $table, $key)
    {
        if (!$this->requireForeignFields($fields, $foreginFields, $table, $key)) {
            return null;
        }
        $return = [];
        foreach($fields as $field) {
            $return[$field] = $this->fields[$field];
        }
        $return[$table] = [];
        foreach($foreginFields as $field) {
            $return[$table][$field] = $this->foreignFields[$table][$field];
        }
        return $return;
    }

    public function setFields($newFields) {
        foreach ($newFields as $field => $value) {
            $this->setField($field, $value);
        }
    }

    public function setField($key, $value) {
        $this->fields[$key] = $value;
    }

    public function setForeignField($table, $field, $value) {
        $this->foreignFields[$table][$field] = $value;
    }

    private function requireFields($requiredFields): bool
    {
        $missingFields = $this->getMissingFields($requiredFields);
        $result = Database::fetchWithFilter(static::TABLE, ["id" => $this->id], $missingFields, false);
        if($result) {
            $this->setFields($result);
            return true;
        }
        //nothing with that id found
        return false;
    }

    private function requireForeignFields($requiredFields, $requiredForeginFields, $table, $key): bool
    {
        $missingFields = $this->getMissingFields($requiredFields);
        if (!isset($this->foreignFields[$table])) {
            $this->foreignFields[$table] = [];
        }
        $missingForeignFields = $this->getMissingKeysFromArray($this->foreignFields[$table], $requiredForeginFields);
        $result = Database::fetchWithJoinAndFilter(
            static::TABLE,
            $table,
            $missingFields,
            $missingForeignFields,
            "id",
            $key,
            [static::TABLE.".id" => $this->id],
            false);

        if(!$result) {
            return  false;
        }

        foreach ($result as $key => $value) {
            [$table, $field] = explode("__", $key, 2);
            if($table == static::TABLE) {
                $this->setField($field, $value);
            }
            else {
                $this->setForeignField($table, $field, $value);
            }
        }
        return true;
    }

    private function getMissingFields($requiredFields): array
    {
        return $this->getMissingKeysFromArray($this->fields, $requiredFields);
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

}