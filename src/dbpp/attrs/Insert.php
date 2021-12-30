<?php


namespace dbpp\attrs;

use Attribute;
use JetBrains\PhpStorm\Pure;
use PDO;

#[Attribute]
class Insert extends Query {
    #[Pure] public function __construct(
        string $query,
        private array $keys = []
    ) {
        parent::__construct($query);
    }

    public function execute(PDO $pdo, array $args): array|bool {
        if(($stmt = $pdo->prepare($this->query))
            &&$this->bindValues($stmt, $args)
            &&$stmt->execute()) {
            $response = [];

            foreach($this->keys as $key)
                $response[] = $pdo->lastInsertId($key);

            return $response;
        }

        return false;
    }
}