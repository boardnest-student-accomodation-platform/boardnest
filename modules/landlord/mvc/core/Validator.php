<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: core/Validator.php
 * Role: Landlord MVC component; read the method notes and its calling controller to understand its responsibilities.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Reusable input validation helpers for text length, numeric range, and allowed-choice values.
 */
namespace BoardNest\Core;
final class Validator {
    /** STUDY - text(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public static function text(mixed $v,string $label,int $min=1,int $max=255): string {
        if(!is_string($v))throw new \DomainException("Invalid $label.");
        $v=trim($v);$len=mb_strlen($v,'UTF-8');
        if($len<$min||$len>$max)throw new \DomainException("$label must be $min-$max characters.");
        return $v;
    }
    /** STUDY - number(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public static function number(mixed $v,string $label,float $min,float $max): float {
        if(!is_scalar($v)||!is_numeric((string)$v))throw new \DomainException("Invalid $label.");
        $n=(float)$v;if(!is_finite($n)||$n<$min||$n>$max)throw new \DomainException("$label is out of range.");
        return $n;
    }
    /** STUDY - choice(): Read parameters, authorization predicates and return value; consider failures and side effects. */
    public static function choice(mixed $v,array $choices,string $label): string {
        if(!is_string($v)||!in_array($v,$choices,true))throw new \DomainException("Invalid $label.");
        return $v;
    }
}
