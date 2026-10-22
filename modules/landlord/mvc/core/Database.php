<?php
declare(strict_types=1);
/**
 * LEARNING EDITION - READ THIS FIRST
 * File: core/Database.php
 * Role: PDO wrapper. Parameter binding separates SQL statements from untrusted user inputs.
 * PHP comments below do not execute and do not change business logic.
 * Flow: HTTP request -> controller -> service -> model -> PDO/database.
 * Security: request values must be validated and scoped to the logged-in user.
 */

/**
 * VIVA GUIDE - FILE PURPOSE
 * Database access wrapper around the existing shared PDO connection. All user-supplied values are passed separately as bound query parameters.
 */
namespace BoardNest\Core;
use PDO;
final class Database {
    private ?PDO $pdo=null;
    /** STUDY - __construct(): Initialization: receive services or dependencies; avoid database writes here. */
    public function __construct(private array $cfg) {}
    /** VIVA: Return the shared PDO connection, creating/loading it lazily. */
    public function pdo(): PDO {
        if ($this->pdo === null) {
            $file = $this->cfg['shared_connection_file'] ?? null;
            if (!is_string($file) || !is_file($file)) {
                throw new \RuntimeException('Shared BoardNest config/db.php is missing.');
            }
            // Scoped include avoids relying on a global $pdo variable.
            $connection = (static function (string $path): ?PDO {
                require $path;
                return isset($pdo) && $pdo instanceof PDO ? $pdo : null;
            })($file);
            if (!$connection instanceof PDO) {
                throw new \RuntimeException('Shared database configuration must define $pdo.');
            }
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->pdo = $connection;
        }
        return $this->pdo;
    }
    /** VIVA: Run a parameterized SELECT and return the first row, if any. */
    public function one(string $sql,array $args=[]): ?array {
        $s=$this->pdo()->prepare($sql);$s->execute($args);$r=$s->fetch();return $r?:null;
    }
    /** VIVA: Run a parameterized SELECT and return all matching rows. */
    public function all(string $sql,array $args=[]): array {
        $s=$this->pdo()->prepare($sql);$s->execute($args);return $s->fetchAll();
    }
    /** VIVA: Fetch one scalar result such as a count or SQL boolean. */
    public function value(string $sql,array $args=[]): mixed {
        $s=$this->pdo()->prepare($sql);$s->execute($args);return $s->fetchColumn();
    }
    /** VIVA: Execute a parameterized write query and return its affected-row count. */
    public function run(string $sql,array $args=[]): int {
        $s=$this->pdo()->prepare($sql);$s->execute($args);return $s->rowCount();
    }
}
