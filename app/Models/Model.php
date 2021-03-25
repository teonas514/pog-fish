<?php

namespace App\Models;

use App\Database;

class Model
{
    public array $fields = [];
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
        var_dump(static::TABLE);
        var_dump($fields);
        Database::insert(static::TABLE, $fields);
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

    public function setFields($newFields) {
        foreach ($newFields as $field => $value) {
            $this->fields[$field] = $value;
        }
    }

    public function setField($key, $value) {
        $this->fields[$key] = $value;
    }

    private function requireFields($requiredFields): bool
    {
        $missingFields = [];
        foreach($requiredFields as $requiredField) {
            if (!in_array($requiredField, $this->fields)) {
                array_push($missingFields, $requiredField);
            }
        }
        $result = Database::fetchWithFilter(static::TABLE, ["id" => $this->id], $missingFields, false);
        if($result) {
            $this->setFields($result);
            return true;
        }
        //nothing with that id found
        return false;
    }


}