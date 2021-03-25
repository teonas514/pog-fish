<?php

namespace App\Models;

use App\Database;

class User extends Model
{
    public const TABLE = "users";

    /*
     * factories
     */
    public static function getUserFromNameAndPassword($name, $password): ?User {
        return User::getWhere(["name" => $name, "password" => $password]);
    }

    public static function createUser($name, $password, $profilePicture):?User {
        self::insert(["name" => $name, "pass" => $password, "picture" => $profilePicture]);
        $user =  User::getUserFromNameAndPassword($name, $password);
        $user->fields["profile_picture"] = $profilePicture;

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
        return $this->getFields("name", "profile_picture");
    }

    public function createPost($name, $body): Post
    {
        return Post::createPost($this->id, $name, $body);
    }
}