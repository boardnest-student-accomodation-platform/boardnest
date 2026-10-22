<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: core/Autoloader.php
 * Role: Landlord MVC component; read the method notes and its calling controller to understand its responsibilities.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Registers the PHP class loader so MVC classes are loaded by namespace only when needed, rather than requiring every file manually.
 */
namespace BoardNest\Core;
final class Autoloader {
    /** STUDY - register(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public static function register(): void {
        spl_autoload_register(static function (string $class): void {
            $prefix='BoardNest\\';
            if (!str_starts_with($class,$prefix)) return;
            $parts=explode('\\',substr($class,strlen($prefix)));
            $basename=array_pop($parts).'.php';
            $directories=array_map('strtolower',$parts);
            $file=dirname(__DIR__).'/'.implode('/',$directories).'/'.$basename;
            if (is_file($file)) require_once $file;
        });
    }
}
