<?php

namespace App\Models;

use App\Database;

class User extends Model
{
    public const TABLE = "users";
    public const DEFAULT_PFP = "/imgs/default_pfp.png";
    public const PFPS = "pfps/";
    public const PFP_EXTENSTION = ".jpg";

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

    private function fromPfpToPath($name): string {
        if(!$name) {
            return self::DEFAULT_PFP;
        }
        // "/" is required due to routing
        return "/" . self::PFPS . $name . self::PFP_EXTENSTION;
    }

    public function display(): ?array
    {
        $fields = $this->requestFields("name", "profile_picture", "money", "bio");
        $fields["profile_picture"] = $this->fromPfpToPath($fields["profile_picture"] ?? false);
        return $fields;
    }

    public function displayPosts(): array
    {
        return Database::fetchWithFilter("posts", ["author_id" => $this->getId()], ["title", "id"]);
    }

    public function createPost($title, $body): Post
    {
        return Post::createPost($this->getId(), $title, $body);
    }


}