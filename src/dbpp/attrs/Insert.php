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

    public function execute(PDO $pdo, array $args): mixed {
        if(($stmt = $pdo->prepare($this->query))
            &&$stmt->execute($args)) {
            $response = [];
            foreach($this->keys as $key)
                $response[] = $key;

            return $response;
        }

        return false;
    }
}