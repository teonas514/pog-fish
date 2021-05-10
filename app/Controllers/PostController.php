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
            return;//todo:redirect to log in
        }
        $title = $_POST["title"] ?? null;
        $body = $_POST["body"] ?? null;

        $tags = [];
        foreach(array_keys($_POST) as $key) {//Change aaa to beetter name
            [$field, $id] = explode("-", $key);
            if ($field === "tag") {
                array_push($tags, $id);
            }
        }

        if(!$title or !$body) {
            return;//todo:redirect back to createView with message
        }
        $post = $user->createPost($title, $body, $tags);
        header("Location: posts/" . $post->getId());
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
        View::render("posts/list.twig", ["posts" => Database::quickFetch("SELECT title, id FROM posts")]);
    }
}