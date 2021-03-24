<?php


namespace App\Controllers;

use App\View;

class PostController
{
    public function createView()
    {
        View::render("posts/create.twig");
    }

    public function create()
    {
        $username = $_POST["name"] ?? null;
        $password = $_POST["password"] ?? null;

    }
}