<?php


namespace App;

class Select
{
    private array $requiredFields;
    private array $requiredForeign;
    private array $requiredManyToMany;

    public function __construct()
    {
        $this->requiredFields = [];
        $this->requiredForeign = [];
        $this->requiredManyToMany = [];
    }

    public function requireFields($fields) {
        $this->requiredFields = $fields;
    }

    public function requireForeignFields($table, $fields, $onKey="id", $foreignKey="id") {
        $this->requiredForeign[$table] = [
            "foreignKey" => $foreignKey,
            "onKey" => $onKey,
            "fields" => $fields
        ];
    }

    public function requireManyToManyFields($table, $linkingTable, $fields, $key = "id") {
        $this->requiredManyToMany[$table] = [
            "key" => $key,
            "linkingTable" => $linkingTable,
            "fields" => $fields
        ];
    }

    private function removeByValue(&$array, $value)
    {
        if (($key = array_search($value, $array)) !== false) { // !== because 0 is falsy D:
            unset($array[$key]);
        }
    }

    public function removeField($field) {
        $this->removeByValue($this->requiredFields, $field);
    }

    public function removeForeignField($table, $field) {
        if ($this->requiredForeign[$table] ?? false) {
            $this->removeByValue($this->requiredForeign[$table]["fields"], $field);
            if(sizeof($this->requiredForeign[$table]["fields"]) <= 0) {
                unset($this->requiredForeign[$table]);
            }
        }
    }

    public function getOnId($table, $id)
    {
        return $this->getWhere($table, [$table => ["id" => $id]]);
    }

    public function getWhere($table, $filterArray, $single = true, $limit = 500)
    {
        if(sizeof($this->requiredForeign) + sizeof($this->requiredFields) + sizeof($this->requiredManyToMany) <= 0) {//unncessary query abort
            if($single) {
                return new Fields();
            }
            else
            {
                return [];
            }
        }

        //figure out if how many we need to fetch
        $all = !($single && sizeof($this->requiredManyToMany) <= 0);
        $fetch = Database::fetchSelect(
            $table,
            $filterArray,
            $this->requiredFields,
            $this->requiredForeign,
            $this->requiredManyToMany,
            $limit,
            $all,
        );
        if($single) {
            if($all) {
                return $this->formatSingle($fetch[0], $fetch, $table);// 1 fields
            }
            return $this->formatSingle($fetch, [$fetch], $table); //1 fields
        }
        return $this->formatMultipule($fetch, $table); //array of fields
    }

    /*
     *  FORMATING
     */

    private function formatMultipule($rows, $table): array
    {
        $array = [];
        foreach ($rows as $row) {
            array_push($array, $this->formatSingle($row, [], $table));
        }
        return $array;
    }

    private function formatSingle($row, $rows, $table): Fields
    {
        $fields = new Fields();
        $fields->table = $this->formatFields($row, $table);
        $fields->foreign = $this->formatForeignFields($row);
        $fields->manyToMany = $this->formatManyToManyFields($rows);
        return $fields;
    }

    private function goThroughFetchData($row, $func): array
    {
        $return = [];
        foreach ($row as $field => $value) {
            [$table, $field] = explode(Database::TABLE_RECOGNICTION_SEPEATOR, $field, 2);
            $func($table, $field, $value, $return);
        }
        return $return;
    }

    private function formatFields(array $row, string $thisTable): array {
        return $this->goThroughFetchData($row, function ($table, $field, $value, &$return) use ($thisTable) {
            if($table === $thisTable) {
                $return[$field] = $value;
            }
        });
    }

    private function formatForeignFields(array $row): array {
        return $this->goThroughFetchData($row, function ($table, $field, $value, &$return) {
            if($this->requiredForeign[$table] ?? false) {
                if(!($return[$table] ?? false)) {
                    $return[$table] = [];
                }
                $return[$table][$field] = $value;
            }
        });
    }

    private function formatManyToManyFields(array $rows): array {
        $manyToManyFields = [];
        foreach ($rows as $row) {
            $fields = $this->goThroughFetchData($row, function ($table, $field, $value, &$return) {
                if($this->requiredManyToMany[$table] ?? false) {
                    if(!($return[$table] ?? false)) {
                        $return[$table] = [];
                    }
                    $return[$table][$field] = $value;
                }
            });
            foreach ($fields as $table => $manyToManyRow) {
                if(!($manyToManyFields[$table] ?? false)) {
                    $manyToManyFields[$table] = [];
                }
                array_push($manyToManyFields[$table], $manyToManyRow);
            }
        }
        return $manyToManyFields;
    }
}