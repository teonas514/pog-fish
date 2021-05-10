<?php

namespace App\Models;

use App\Database;

class User extends Model
{
    public const TABLE = "users";
    public const DEFAULT_PFP = "/imgs/default_pfp.png";
    public const PFPS = "pfps/";
    public const PFP_EXTENSTION = "jpg";

    /*
     * factories
     */
    public static function getUserFromNameAndPassword($name, $password): ?User {
        return User::getWhere(["name" => $name, "password" => $password]);
    }

    public static function createUser($name, $password):?User {
        return self::insert(["name" => $name, "password" => $password]);
    }

    public static function getLoggedInUser():?User
    {
        $name = $_SESSION["name"] ?? false;
        $id = $_SESSION["id"] ?? false;

        if ($name and $id) {
            return new User($id, ["name" => $name]);
        }
        return null;
    }
    /*
     * methods
     */
    public function logIn()
    {
        $_SESSION = $this->requestFields("name", "id");
    }

    public static function pfpNameToPath($name): string
    {
        return self::PFPS . $name . "." . self::PFP_EXTENSTION;
    }


    public function display(): ?array
    {
        $this->requireFields(["name", "profile_picture", "money"]);
        $this->requireForeignFields("roles", ["name", "rank"], "id", "role_id");
        $this->fetch();

        if($fields["profile_picture"] ?? false) {
            $fields["profile_picture"] = "/" . self::pfpNameToPath($fields["profile_picture"]);
        }
        else {
            $fields["profile_picture"] = self::DEFAULT_PFP;
        }
        return $fields;
    }

    public function displayPosts(): array
    {
        return Database::fetchWithFilter("posts", ["author_id" => $this->getId()], ["title", "id"]);
    }

    public function createPost(...$params): Post
    {
        return Post::createPost($this->getId(), ...$params);
    }


}