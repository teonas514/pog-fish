<?php

namespace App\Models;

use App\Database;

class User extends Model
{
    public const TABLE = "users";
    public const DEFAULT_PFP = "/imgs/default_pfp.png";

    /*
     * factories
     */
    public static function getUserFromNameAndPassword($name, $password): ?User {
        return User::getWhere(["name" => $name, "password" => $password]);
    }

    public static function createUser($name, $password):?User {
        self::insert(["name" => $name, "password" => $password]);
        $user =  User::getUserFromNameAndPassword($name, $password);

        return $user;
    }

    public static function getLoggedInUser():?User
    {
        $name = $_SESSION["name"] ?? false;
        $id = $_SESSION["id"] ?? false;

        if ($name and $id) {
            $user = new User($id);
            $user->fields["name"] = $name;
            return $user;
        }
        return null;
    }
    /*
     * methods
     */
    public function logIn()
    {
        $_SESSION = $this->getFields("name", "id");
    }

    public function display(): ?array
    {
        $fields = $this->getFields("name", "profile_picture", "money");
        $fields["profile_picture"] = $fields["profile_picture"] ? $fields["profile_picture"] : self::DEFAULT_PFP;
        return $fields;
    }

    public function displayPosts(): array
    {
        return Database::fetchWithFilter("posts", ["author_id" => $this->id], ["title", "id"]);
    }

    public function createPost($title, $body): Post
    {
        return Post::createPost($this->id, $title, $body);
    }
}