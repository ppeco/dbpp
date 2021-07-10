<?php


namespace dbpp\attrs;

use Attribute;
use JetBrains\PhpStorm\Pure;
use PDO;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

#[Attribute]
class Update extends Query {
    #[Pure] public function __construct(
        string $query
    ) {
        parent::__construct($query);
    }

    public function execute(PDO $pdo, array $args): bool {
        return ($stmt = $pdo->prepare($this->query))
            &&$stmt->execute($args);
    }
}