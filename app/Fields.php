<?php

namespace App;

class Fields
{
    public array $table = []; //Fields that exist in the same table as the ID
    public array $foreign = []; //Fields that are joined on the ID
    public array $manyToMany = []; //List of fields that are joined on the ID (requires table linking in between)
}