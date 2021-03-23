<?php
namespace App;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

class View
{
    private ?Environment $twig;
    private static ?View $instance = null;

    protected function __construct()
    {
        $loader = new FilesystemLoader('../app/views');
        $this->twig = new Environment($loader, []);
    }

    private static function getTwig(): ?Environment
    {
        return static::getInstance()->twig;
    }

    public static function getInstance(): ?View
    {
        if (self::$instance === null) {
            self::$instance = new View();
        }
        return self::$instance;
    }

    public static function render($page, $data = [])
    {
        try {
            $template = self::getTwig()->load($page);
            echo $template->render($data);
        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            echo "twig load error";
        }
    }
}
