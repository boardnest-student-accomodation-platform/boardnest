<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: core/View.php
 * Role: Landlord MVC component; read the method notes and its calling controller to understand its responsibilities.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Locates template files, renders views using supplied data, and wraps authenticated pages with the common Landlord layout.
 */
namespace BoardNest\Core;
final class View {
    /** STUDY - __construct(): Initialization: receive services or dependencies; avoid database writes here. */
    public function __construct(private string $root) {}
    /** VIVA: Resolve only a safe module-relative view name and capture HTML with output buffering. */
    public function render(string $name,array $data=[]): string {
        if(!preg_match('~^[a-z]+/[a-z0-9_/-]+$~',$name)||str_contains($name,'..'))
            throw new \InvalidArgumentException('Invalid view path.');
        [$domain,$relative]=explode('/',$name,2);
        $file=$this->root.'/modules/'.$domain.'/views/'.$relative.'.php';
        if(!is_file($file))throw new \RuntimeException('View not found: '.$name);
        extract($data,EXTR_SKIP);ob_start();
        try {require $file;return (string)ob_get_clean();}
        catch(\Throwable $e){ob_end_clean();throw $e;}
    }
    /** VIVA: Wrap feature-specific HTML in the shared Landlord navigation/header page. */
    public function layout(string $content,string $title,array $landlord,string $active='dashboard'): string {
        $file=$this->root.'/shared/views/layouts/landlord.php';
        $flash=Session::pullFlash();ob_start();
        try {require $file;return (string)ob_get_clean();}
        catch(\Throwable $e){ob_end_clean();throw $e;}
    }
}
