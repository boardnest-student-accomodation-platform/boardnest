<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: core/Session.php
 * Role: Landlord MVC component; read the method notes and its calling controller to understand its responsibilities.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Starts secure session cookies and manages one-request flash notifications shown after a redirect.
 */
namespace BoardNest\Core;
final class Session {
    /** VIVA: Use HttpOnly and SameSite cookies; mark cookies Secure when HTTPS is available. */
    public static function start(string $path='/'): void {
        if(session_status()===PHP_SESSION_ACTIVE)return;
        ini_set('session.use_strict_mode','1');
        $secure=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off');
        session_set_cookie_params(['lifetime'=>0,'path'=>$path,'secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);
        session_start();
    }
    /** VIVA: Store a message for the next request after redirect. */
    public static function flash(string $type,string $message): void {$_SESSION['flash']=[$type,$message];}
    /** VIVA: Read once and remove a flash message from the session. */
    public static function pullFlash(): ?array {
        $f=$_SESSION['flash']??null;unset($_SESSION['flash']);return is_array($f)?$f:null;
    }
}
