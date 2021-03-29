<?php


namespace App\Controllers;

use App\Database;
use App\Models\Post;
use App\Models\User;
use App\View;

class PostController
{
    public function createView()
    {
        View::render("posts/create.twig");
    }

    public function create()
    {
        $user = User::getLoggedInUser();
        if(!$user) {
            return;//todo:redirect to log in
        }
        $name = $_POST["name"] ?? null;
        $body = $_POST["body"] ?? null;
        if(!$name or !$body) {
            return;//todo:redirect back to createView with message
        }

        $post = $user->createPost($name, $body);
        header("Location: posts/" . $post->id);
    }

    public function show($vars) {
        $id = $vars["id"];
        $post = new Post($id);
        if($post) {
            $display = $post->display();
            if($display) {
                View::render("posts/show.twig",["post" => $display]);
            }
        }
    }

    public function list() {
        View::render("posts/list.twig", ["posts" => Database::quickFetch("SELECT name, id FROM posts")]);
    }
}