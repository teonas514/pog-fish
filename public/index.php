<?php
require '../vendor/autoload.php';
session_start();

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', 'HomeController::home');
    $r->addRoute('GET', '/log-in', 'UserController::logIn');
    $r->addRoute('GET', '/register', 'UserController::register');
    $r->addRoute('POST', '/security-check', 'UserController::secruityCheck');
    $r->addRoute('GET', '/users/{id:\d+}', 'UserController::show');


    // {id} must be a number (\d+)
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        echo "404";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        echo "405";
        // ... 405 Method Not Allowed
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        [$class, $method] = explode("::", $handler, 2);
        $class = "App\\Controllers\\" . $class;
        call_user_func([new $class, $method], $vars);
        // ... call $handler with $vars
        break;
}