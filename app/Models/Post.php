<?php


namespace App\Models;


use App\Database;

class Post extends Model
{
    public const TABLE = "posts";

    public static function createPost($authorId, $name, $body):?Post {
        $fields = ["author_id" => $authorId, "name" => $name, "body" => $body];
        self::create($fields);
        $post = self::getWhere(["name" => $name]);
        $post->setFields($fields);
        return $post;
    }

    public function display(): ?array
    {
        return $this->getFields("name", "body");
    }
}