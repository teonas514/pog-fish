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

    private function overlayFields($newFields): void
    {
        foreach ($newFields as $field => $value) {
            $this->fields[$field] = $value;
        }
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
            $this->overlayFields($result);
            return true;
        }
        //nothing with that id found
        return false;
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
}