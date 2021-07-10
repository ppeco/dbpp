<?php


namespace dbpp;


use JetBrains\PhpStorm\Deprecated;
use PDO;

#[Deprecated]
class DBPP {
    #[Deprecated("Use your database constructor")]
    public static function init(Database $database, PDO $pdo): void {}

    #[Deprecated("Use your database constructor")]
    public static function initDao(string $classname, PDO $pdo): object { return new $classname($pdo); }
}