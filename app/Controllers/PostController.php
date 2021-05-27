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
        $tags = Database::quickFetch("SELECT name, id FROM tags");
        View::render("posts/create.twig", ["tags" => $tags]);
    }

    public function create()
    {
        $user = User::getLoggedInUser();
        if(!$user) {
            //header("Location : /log-in");
            header("Location: /log-in");
            return;
        }
        $title = $_POST["title"] ?? null;
        $body = $_POST["body"] ?? null;
        $tags = array_values($_POST["tags"]);

        if(!$title or !$body) {
            return;//todo:redirect back to createView with error message
        }
        $post = $user->createPost($title, $body, $tags);
        header("Location: /posts/" . $post->getId());
    }

    public function show($vars) {
        $id = $vars["id"];
        $post = new Post($id);
        if($post) {
            $display = $post->display();
            if($display) {
                View::render("posts/show.twig", $display);
            }
        }
    }

    public function list() {
        View::render("posts/list.twig", ["posts" => Database::quickFetch("SELECT title, id FROM posts limit 16")]);
    }
}