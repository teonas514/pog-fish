<?php


namespace App\Models;


use App\Database;

class Post extends Model
{
    public const TABLE = "posts";
    private array $tags = [];

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

    public static function createPost($authorId, $title, $body, $tags = []):?Post {
        $fields = ["author_id" => $authorId, "title" => $title, "body" => $body];
        $post = self::insert($fields);
        $post->addTags($tags);
        return $post;
    }

    public function addTags($tags) { //optimize to one insert query
        foreach ($tags as $tag) {
            Database::insert("post_tags", [
                "post_id" => $this->getId(),
                "tag_id" => $tag
            ]);
        }
    }

    public function display(): ?array
    {
        $this->requireFields(["title", "body"]);
        $this->requireManyToManyFields("tags", "post_tags", ["name", "description"]);
        $this->fetch();
        return [];
    }
}