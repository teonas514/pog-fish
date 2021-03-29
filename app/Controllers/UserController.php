<?php


namespace App\Controllers;
use App\Database;
use App\Models\User;
use App\View;
use Intervention\Image\ImageManagerStatic as Image;

class UserController
{
    public function logIn() {
        View::render("users/logIn.twig");
    }

    public function register() {
        View::render("users/register.twig");
    }

    public function checkCredentials() {
        $username = $_POST["name"] ?? null;
        $password = $_POST["password"] ?? null;
        $user = null;
        if(($_POST["register"] ?? false) !== false) {
//            $imagePath = $_FILES['profile-picture']['tmp_name'];
//            $image = Image::make($imagePath);
//            $image->resize(32,32);
//            $image->encode("jpg", 0);
//            $profilePicture = utf8_encode((string) $image->encode('data-url')); //blob
            $user = User::createUser($username, $password);
        } else { //if ($_POST["log-in"] ?? false) {
            $user = User::getUserFromNameAndPassword($username, $password);
        }
        $user->logIn();
        header("Location: /");
    }

    public function show($vars) {
        $id = $vars["id"];
        $user = new User($id);
        if($user) {
            $display = $user->display();
            if($display) {
                View::render("users/show.twig",["user" => $display, "posts" => $user->displayPosts()]);
            }
        }
    }

    public function list() {
        View::render("users/list.twig", ["users" => Database::quickFetch("SELECT name, id FROM users")]);
    }
}