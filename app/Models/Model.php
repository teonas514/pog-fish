<?php

namespace App\Models;

use App\Database;
use App\Fields;
use App\Select;

/*
 * assumes serial = "id"
 */
class Model
{
    private array $setFields = []; // fields that have been set by php

    private Fields $fields;

    private int $id;
    public const TABLE = "";
    public const ID = "id";



    public function __construct($id, $fields = null)
    {
        $this->id = $id;
        $this->fields = $fields ?? new Fields();
    }

    /*
     *        factories
     */

    private static function createByFields($fields)//: ?static
    {
        $id = $fields->table[static::ID] ?? false;
        if($id !== false) {
            unset($fields->table[static::ID]);
            return new static($id, $fields);
        }
        return null;
    }

    //returns instance of which ever class called it (return type static)
    public static function getWhere($filter, $tableFields = [], $all = false)//: ?static
    {
        $select = new Select();
        array_push($tableFields, static::ID);
        $select->requireFields($tableFields);
        $return = $select->getWhere(static::TABLE, [static::TABLE => $filter], !$all);
        if(!$all) {
            return static::createByFields($return); //return will be fields
        }
        $models = [];//return will be an array of fields
        foreach ($return as $fields) {
            array_push($models, self::createByFields($fields));
        }
        return $models;
    }

    //
    public static function insert($tableFields)//: ?static
    {
        $id = Database::insert(static::TABLE, $tableFields, true);
        $fields = new Fields();
        $fields->table = $tableFields;
        return new static($id, $fields);
    }

    /*
     * getters
     */

    public function getField($field): ?string {
        return $this->setFields[$field] ?? ($this->fields->table[$field] ?? null);
    }

    public function getFields(): array
    {
        return array_merge($this->fields->table, $this->setFields);
    }

    public function getForeignFields($table): array {
        return $this->fields->foreign[$table] ?? [];
    }

    public function getManyToManyFields($table): array {
        return $this->fields->manyToMany[$table] ?? [];
    }

    public function getId(): int
    {
        return $this->id;
    }

    /*
     * public setters
     */

    public function setField($key, $value) {
        $this->setFields[$key] = $value;
        $tableValue = $this->tableFields[$key] ?? null;

        if((($tableValue))) {
            if($tableValue == $value) {
                unset($this->setFields[$key]);
                return;
            }
        }
    }

    private function filterSelect(Select &$select) {
        foreach ($this->fields->table as $field => $_) {
            $select->removeField($field);
        }
        foreach ($this->fields->foreign as $table => $fields) {
            foreach ($fields as $field => $_) {
                $select->removeForeignField($table, $field);
            }
        }

    }

    public function fetch(Select $select) {
        $this->filterSelect($select); //dont fetch on stuff we already know
        $fields = $select->getOnId(static::TABLE, $this->id);
        $this->overlayFields($fields);
    }

    /*
     * save
     */

    public function save()
    {
        Database::update(static::TABLE, $this->setFields, $this->id);
    }

    /*
     * overlay another field after fetch
     */

    private function overlayFields($fields) {
        $this->fields->table = array_merge($fields->table, $this->fields->table);
        $this->overlayForeignFields($fields->foreign);
        $this->addManyToManyFields($fields->manyToMany);
    }

    private function overlayForeignFields(array $foreignFields)
    {
        foreach ($foreignFields as $table => $fields) {
            if(!($this->fields->foreign[$table] ?? false)) {
                $this->fields->foreign[$table] = [];
            }
            $this->fields->foreign = array_merge($this->fields->foreign, $foreignFields);
        }
    }

    private function addManyToManyFields(array $manyToManyFields)
    {
        foreach ($manyToManyFields as $table => $rows) {
            if(!($this->fields->manyToMany[$table] ?? false)) {
                $this->fields->manyToMany[$table] = [];
            }
            foreach ($rows as $row) {
                array_push($this->fields->manyToMany[$table], $row);
            }
        }
    }
}