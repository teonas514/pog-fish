<?php

namespace App\Models;

use App\Database;

/*
 * assumes serial = "id"
 */
class Model
{
    private array $tableFields = []; // fields that come from table
    private array $setFields = []; // fields that have been set by php
    private array $foreignFields = []; // fields that come from foreing tables (CAN'T BE SET BY PHP)
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

    public static function insert($fields)
    {
        $id = Database::insert(static::TABLE, $fields, true);
        return new static($id, $fields);
    }

    /*
     * getter
     */

    public function getField($field): ?string {
        return $this->setField[$field] ?? $this->tableFields[$field] ?? null;
    }

    private function getAllFields(): array
    { // overlays fields with set fields
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
     *  requests table values
     */

    public function requestFields(...$fields): ?array {
        if(!$this->requireFields($fields)) {
            return null;//couldnt require those fields
        }
        $return = [];
        foreach($fields as $field) {
            $return[$field] = $this->getAllFields()[$field];
        }
        return $return;
    }

    public function requestFieldsWithForeginFields($fields, $foreginFields, $table, $key): ?array
    {
        if (!$this->requireForeignFields($fields, $foreginFields, $table, $key)) {
            return null;
        }
        $return = [];
        foreach($fields as $field) {
            $return[$field] = $this->tableFields[$field];
        }
        $return[$table] = [];
        foreach($foreginFields as $field) {
            $return[$table][$field] = $this->foreignFields[$table][$field];
        }
        return $return;
    }

    /*
     *  require
     */

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
                $this->tableFields[$field] = $value;
            }
            else {
                $this->setForeignField($table, $field, $value);
            }
        }
        return true;
    }

    public function save()
    {
        Database::update(static::TABLE, $this->setFields, $this->id);
        //Database::executeWithBoundParams("UPDATE " .. static::TABLE .. "", );
    }
}