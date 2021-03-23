<?php

namespace App\Models;

use App\Database;

class User extends Model
{
    public const TABLE = "users";

    /*
     * fabriker
     */
    public static function getUserFromNameAndPassword($name, $password): ?User {
        $result = Database::fetchWithFilter("users",
                ["name" => $name, "password" => $password],
                ["id"],
                false);
        if ($result) {
            return new User($result["id"]);
        }
        return null;
    }

    public static function createUser($name, $password, $profilePicture):?User {
        Database::executeWithBoundParams("INSERT INTO users (name, password, profile_picture) VALUES (:name, :pass, :picture)",
            [":name" => $name, ":pass" => $password, ":picture" => $profilePicture]);
        $user = User::getUserFromNameAndPassword($name, $password); //get id of the user we just created

        $user->fields["name"] = $name;
        $user->fields["password"] = $password;
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

    public function logIn()
    {
        $_SESSION = $this->getFields("name", "id");
    }

    public function display(): ?array
    {
        return $this->getFields("name", "profile_picture");
    }
}