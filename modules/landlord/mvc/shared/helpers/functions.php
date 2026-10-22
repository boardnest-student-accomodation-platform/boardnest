<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: shared/helpers/functions.php
 * Role: Landlord MVC component; read the method notes and its calling controller to understand its responsibilities.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Presentation helpers used by Landlord templates, including output escaping and base-URL generation. Escape dynamic text before including it in HTML.
 */
use BoardNest\Core\CSRF;
function url(string $path='',array $query=[]): string {
    $base=rtrim(BOARDNEST_BASE,'/');$path='/'.ltrim($path,'/');
    return $base.($path==='/'?'/':$path).($query?'?'.http_build_query($query,'','&',PHP_QUERY_RFC3986):'');
}
function e(mixed $value): string {return htmlspecialchars((string)($value??''),ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function csrf_field(): string {return '<input type="hidden" name="_csrf" value="'.e(CSRF::token()).'">';}
function cash(mixed $value): string {return 'LKR '.number_format((float)$value,2);}
function date_label(mixed $value): string {return $value?date('d M Y',strtotime((string)$value)):'—';}
function pill(mixed $value): string {return ucwords(str_replace('_',' ',(string)$value));}
function int_field(mixed $value,int $min=1,int $max=10000000): int {
    if(!is_scalar($value)||filter_var($value,FILTER_VALIDATE_INT)===false||(int)$value<$min||(int)$value>$max)
        throw new DomainException("Enter a number between $min and $max.");
    return (int)$value;
}

// Optional mbstring fallbacks for development PHP installations without ext-mbstring.
if(!function_exists('mb_strlen')){function mb_strlen(string $value,?string $encoding=null): int {return strlen($value);}}
if(!function_exists('mb_substr')){function mb_substr(string $value,int $start,?int $length=null,?string $encoding=null): string {return substr($value,$start,$length);}}
if(!function_exists('mb_strtoupper')){function mb_strtoupper(string $value,?string $encoding=null): string {return strtoupper($value);}}
if(!function_exists('mb_strimwidth')){function mb_strimwidth(string $value,int $start,int $width,string $trimmarker='',?string $encoding=null): string {return strlen($value)<=$start+$width?substr($value,$start):substr($value,$start,max(0,$width-strlen($trimmarker))).$trimmarker;}}

/**
 * VIVA: Derive the visual room availability state from actual slot counts.
 * Do not infer fully booked from rooms.status; that column is a publishing flag.
 */
function room_availability_state(array $counts): array {
    $total = (int)($counts['total_slots'] ?? $counts['slot_capacity'] ?? 0);
    $available = (int)($counts['available_slots'] ?? 0);
    $reserved = (int)($counts['reserved_slots'] ?? 0);
    $occupied = (int)($counts['occupied_slots'] ?? 0);
    if ($total < 1) return ['Unavailable','unavailable'];
    if ($available === $total) return ['Available','available'];
    if ($available > 0) return ['Partially booked','partial'];
    if ($reserved === $total) return ['Fully reserved','full'];
    if ($occupied === $total) return ['Fully occupied','full'];
    if ($reserved + $occupied === $total) return ['Fully booked','full'];
    return ['Unavailable','unavailable']; // Includes blocked maintenance slots.
}
