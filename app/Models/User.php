<?php

namespace App\Models;

use App\Database;
use App\Fields;
use App\Select;

class User extends Model
{
    public const TABLE = "users";
    public const DEFAULT_PFP = "/imgs/default_pfp.png";
    public const PFPS = "pfps/";
    public const PFP_EXTENSTION = "jpg";

    static private User $loggedInUser;
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
        if(self::$loggedInUser ?? false) {
            return self::$loggedInUser;
        }
        if(!($_SESSION["logged-in-user"] ?? false)) return null;
        $name = $_SESSION["logged-in-user"]["name"];
        $id = $_SESSION["logged-in-user"]["id"];
        $pfp = $_SESSION["logged-in-user"]["profile-picture"];
        $fields = new Fields();
        $fields->table = [
            "name" => $name,
            "profile_picture" => $pfp
        ];
        return new User($id, $fields);
    }

    public static function pfpNameToPath($name): string
    {
        return self::PFPS . $name . "." . self::PFP_EXTENSTION;
    }

    /*
     * methods
     */

    public function logIn()
    {
        $select = new Select();
        $select->requireFields(["name", "profile_picture"]);
        $this->fetch($select);
        $_SESSION["logged-in-user"] = [
            "name" => $this->getField("name"),
            "profile-picture" => $this->getField("profile_picture"),
            "id" => $this->getId()
        ];
    }

    private function getProfilePicture(): string
    {
        if ($this->getField("profile_picture") === null) {
            return self::DEFAULT_PFP;
        }
        else {
            return  "/" . self::pfpNameToPath($this->getField("profile_picture"));
        }
    }

    public function getFieldsWithProfilePicture(): array {
        $fields = $this->getFields();
        $fields["profile_picture"] = $this->getProfilePicture();
        return $fields;
    }

    public function display(): ?array
    {
        $select = new Select();
        $select->requireFields(["name", "profile_picture", "bio"]);
        $select->requireForeignFields("roles", ["name", "rank"], "role_id");
        $this->fetch($select);
        return [
            "user" => $this->getFieldsWithProfilePicture(),
            "role" => $this->getForeignFields("roles")
        ];
    }

    public function headerDisplay(): array {
        $display = $this->getFieldsWithProfilePicture();
        $display["id"] = $this->getId();
        return $display;
    }

    public function displayPosts(): array
    {
        $posts = Post::GetWhere(["author_id" => $this->getId()], ["title"], true);
        $display = [];
        foreach ($posts as $post) {
            array_push($display, [
                "title" => $post->getFields()["title"],
                "id" => $post->getId()
            ]);
        }
        return $display;
    }

    public function createPost(...$params): Post
    {
        return Post::createPost($this->getId(), ...$params);
    }
}