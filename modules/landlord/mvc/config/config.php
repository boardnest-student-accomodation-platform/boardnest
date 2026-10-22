<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: config/config.php
 * Role: Landlord MVC component; read the method notes and its calling controller to understand its responsibilities.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Landlord-specific configuration. The app base URL, debug switch, shared PDO config location, and upload size live here. Do not store public-facing secrets in this file.
 */
// Keep local development credentials here; never commit real production secrets.
return [
    'name' => 'BoardNest',
    'base_path' => '/boardnest-merged/public/landlord',
    'debug' => false, // Never expose diagnostics on a public site.
    'db' => ['shared_connection_file' => dirname(__DIR__, 4) . '/config/db.php'],
    'upload_max_bytes' => 5*1024*1024,
];
