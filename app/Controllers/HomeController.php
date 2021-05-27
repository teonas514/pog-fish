<?php

namespace App\Controllers;
use App\Models\User;
use App\Select;
use App\View;

class HomeController
{
    public function __constructor()
    {

    }

    public function updateLayout() {
        $id = (int)$_POST["layouts"];
        $user = User::getLoggedInUser();
        $user->setField("layout_id", $id);
        $user->save();
        header("Location: /");
    }

    private function toIdArray($fieldsArray): array {
        $array = [];
        foreach ($fieldsArray as $fields) {
            array_push($array, $fields->table["id"]);
        }
        return $array;
    }

    public function home()
    {
        $user = User::getLoggedInUser();
        if(!$user) {
            View::render("home.twig");
            return;
        }
        $select = new Select();
        $select->requireFields(["name", "money", "profile_picture", "bio"]);
        $select->requireForeignFields("roles", ["rank", "name"], "role_id");
        $select->requireForeignFields("layouts", ["template_areas"], "layout_id");
        $user->fetch($select);
        $select = new Select();
        $select->requireFields(["id"]);
        $fieldsArray = $select->getWhere("layouts", [], false);

        View::render("home.twig", [
            "user" => $user->display(),
            "posts" => $user->displayPosts(),
            "template_areas" => json_decode($user->getForeignFields("layouts")["template_areas"]),
            "layouts" => $this->toIdArray($fieldsArray)
        ]);
    }
}