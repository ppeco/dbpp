<?php


namespace dbpp\attrs;

use Attribute;
use PDO;

#[Attribute]
class Query {
    public function __construct(
        protected string $query
    ) {}

    public function execute(PDO $pdo, array $args): mixed {
        if(($stmt = $pdo->prepare($this->query))
                &&$stmt->execute($args)) {
            return $stmt->fetchAll();
        }

        return false;
    }
}