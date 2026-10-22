<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: core/Router.php
 * Role: Maps HTTP path and method to a controller handler; routes do not enforce database constraints alone.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Maps a request method and URL path to exactly one controller method. Unknown routes return 404 and unsupported methods return 405.
 */
namespace BoardNest\Core;
final class Router {
    /** STUDY - __construct(): Initialization: receive services or dependencies; avoid database writes here. */
    public function __construct(private array $routes) {}
    /** VIVA: Check both URL path and HTTP verb; instantiate only the class configured for that route. */
    public function dispatch(App $app): never {
        $method=$app->request()->method();$path=$app->request()->path();
        $matchedPath=false;
        foreach($this->routes as [$verb,$route,$class,$action]){
            if($path!==$route)continue;
            $matchedPath=true;
            if($verb!==$method)continue;
            if(!class_exists($class)||!method_exists($class,$action))throw new \LogicException('Broken route: '.$route);
            $controller=new $class($app);
            $controller->$action();
            throw new \LogicException('Controller returned without sending a response: '.$route);
        }
        if($matchedPath)$app->response()->error(405,'Method not allowed');
        $app->response()->error(404,'Page not found');
    }
}
