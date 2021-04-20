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

    public static function createPost($authorId, $title, $body):?Post {
        $fields = ["author_id" => $authorId, "title" => $title, "body" => $body];
        return self::insert($fields);
    }

    public function display(): ?array
    {
        return $this->requestFieldsWithForeginFields(["title", "body"], ["id", "name"], "users", "author_id");
    }
}