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
        if(($_POST["register"] ?? fALSe) !== FAlse) {
            $user = User::createUser($username, $password);
        } else { //if ($_POST["log-in"] ?? false) {
            $user = User::getUserFromNameAndPassword($username, $password);
        }
        $user->logIn();
        header("Location: /");
    }

    public function show($vars) {
        $id = (int)$vars["id"];
        $user = null;
        $isOwner = false;
        $loggedInUser = User::getLoggedInUser();

        if ($loggedInUser) {
            if ($loggedInUser->getId() === $id) {
                $user = $loggedInUser;
                $isOwner = true;
            }
        }
        if(!$user) {
            $user = new User($id);
        }
        View::render("users/show.twig",["user" => $user->display(),
            "posts" => $user->displayPosts(),
            "isOwner" =>  $isOwner
        ]);
    }

    public function list() {
        View::render("users/list.twig", ["users" => Database::quickFetch("SELECT name, id FROM users")]);
    }

    private function getProfilePicture(): ?string
    {
        $imagePath = $_FILES['profile-picture']['tmp_name'];
        if($_FILES["profile-picture"]["error"] !== 0) {
            return null;
        }
        $image = Image::make($imagePath);
        $image->resize(128, 128);
        $image->encode(User::PFP_EXTENSTION, 0);
        $unique = substr(base64_encode(md5( mt_rand() )), 0, 15);
        $image->save(User::pfpNameToPath($unique));
        return $unique;
    }

    public function update() {
        $user = User::getLoggedInUser();
        if(!$user) { // a user have to be logged in
            return;
        }
        $pfp = $this->getProfilePicture();
        if($pfp) {
            $user->setField("profile_picture", $pfp);
        }
        $user->setField("bio", $_POST["bio"] ?? "");
        $user->save();
        header("Location: /users/". $user->getId());
    }

    public function edit() {
        $loggedInUser = User::getLoggedInUser();
        if(!$loggedInUser) {//no logged in user
            header("Location: /log-in");
            return;
        }
        $display = $loggedInUser->display();
        View::render("users/edit.twig", $display);
    }

    public function profile() {
        $user = User::getLoggedInUser();
        header("Location: /users/". $user->getId());
    }

    public function logOut() {
        unset($_SESSION["logged-in-user"]);
        header("Location: /log-in");
    }
}