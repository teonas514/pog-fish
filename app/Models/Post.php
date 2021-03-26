<?php


namespace App\Models;


use App\Database;

class Post extends Model
{
    public const TABLE = "posts";

    public static function getAllPostsFrom($user): array {
        $results = Database::fetchWithFilter(static::TABLE, ["author_id" => $user->id], ["name", "id"]);
        $posts = [];
        foreach ($results as $result) {
            $post = new Post($result["id"]);
            $post->setField("name", $result["name"]);
            array_push($posts, $post);
        }
        return $posts;
    }

    public static function createPost($authorId, $name, $body):?Post {
        $fields = ["author_id" => $authorId, "name" => $name, "body" => $body];
        self::insert($fields);
        $post = self::getWhere(["name" => $name]);
        $post->setFields($fields);
        return $post;
    }

    public function display(): ?array
    {
        return $this->getFieldsWithJoin(["name", "body"], ["id", "name"], "users", "author_id");
    }
}