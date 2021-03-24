<?php

namespace App\Controllers;
use App\Models\User;
use App\View;

class HomeController
{
    public function __constructor()
    {

    }

    public function home()
    {
        $data = [];
        $user = User::getLoggedInUser();
        if ($user) {
            $display = $user->display();
            if ($display) {
                $data = $display;
            }
        }
        View::render("home.twig", $data);
    }
}