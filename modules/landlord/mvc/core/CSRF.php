<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: core/CSRF.php
 * Role: Cross-site request forgery token handling; destructive POST requests require token verification.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Creates and checks per-session CSRF tokens. State-changing HTML forms must submit a matching token to prevent unauthorized cross-site requests.
 */
namespace BoardNest\Core;
final class CSRF {
    /** VIVA: Get or create the session token used in hidden POST form fields. */
    public static function token(): string {
        if (empty($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(32));
        return $_SESSION['csrf'];
    }
    /** VIVA: Use constant-time token comparison to validate a submitted value. */
    public static function verify(?string $value): bool {
        return is_string($value)&&isset($_SESSION['csrf'])&&hash_equals($_SESSION['csrf'],$value);
    }
    /** VIVA: Stop the request when the submitted token is invalid. */
    public static function enforce(): void {
        if (!self::verify(is_string($_POST['_csrf']??null)?$_POST['_csrf']:null)) {
            throw new \DomainException('Your form session expired. Refresh and try again.');
        }
    }
}
