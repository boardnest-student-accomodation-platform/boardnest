<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: core/Request.php
 * Role: Landlord MVC component; read the method notes and its calling controller to understand its responsibilities.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Converts the current HTTP request into safe-to-read route paths, fields, numeric IDs, and uploaded-file data.
 */
namespace BoardNest\Core;
final class Request {
    /** STUDY - __construct(): Initialization: receive services or dependencies; avoid database writes here. */
    public function __construct(private string $base) {}
    /** STUDY - method(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function method(): string {return strtoupper($_SERVER['REQUEST_METHOD']??'GET');}
    /** VIVA: Normalize the requested URL relative to the configured Landlord base path. */
    public function path(): string {
        $path=parse_url($_SERVER['REQUEST_URI']??'/', PHP_URL_PATH);
        $path=is_string($path)?$path:'/';
        $base=rtrim($this->base,'/');
        if ($base!=='' && ($path===$base || str_starts_with($path,$base.'/')))
            $path=substr($path,strlen($base));
        if ($path===''||$path==='/'||$path==='/index.php')return '/';
        return rtrim('/'.ltrim($path,'/'),'/');
    }
    /** STUDY - field(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public function field(string $key,string $default=''): string {
        $v=$_POST[$key]??$default;return is_string($v)?trim($v):$default;
    }
    /** VIVA: Validate a positive identifier before passing it to models. */
    public function id(string $key='id'): int {
        $v=$_POST[$key]??$_GET[$key]??0;
        return (is_scalar($v)&&filter_var($v,FILTER_VALIDATE_INT)!==false && (int)$v>0)?(int)$v:0;
    }
    /** VIVA: Access PHP uploaded-file data, if a file was included. */
    public function file(string $key): ?array {
        return isset($_FILES[$key])&&is_array($_FILES[$key])?$_FILES[$key]:null;
    }
}
