<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: core/Response.php
 * Role: Landlord MVC component; read the method notes and its calling controller to understand its responsibilities.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Centralizes HTML/JSON responses, local redirects, and user-safe error pages; response methods terminate the request.
 */
namespace BoardNest\Core;
final class Response {
    /** STUDY - html(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function html(string $body,int $status=200): never {
        http_response_code($status);header('Content-Type: text/html; charset=utf-8');echo $body;exit;
    }
    /** STUDY - json(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function json(array $data,int $status=200): never {
        http_response_code($status);header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);exit;
    }
    /** STUDY - redirect(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function redirect(string $path,array $query=[]): never {
        header('Location: '.url($path,$query),true,303);exit;
    }
    /** STUDY - error(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function error(int $status,string $msg): never {
        http_response_code($status);header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><html><meta name="viewport" content="width=device-width,initial-scale=1"><title>Error</title><body style="font-family:system-ui;padding:3rem"><h1>'.e($status).' — '.e($msg).'</h1><p><a href="'.e(url('/')).'">Go to BoardNest</a></p></body></html>';exit;
    }
}
