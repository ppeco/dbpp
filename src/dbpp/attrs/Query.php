<?php


namespace dbpp\attrs;

use Attribute;
use PDO;
use PDOStatement;

#[Attribute]
class Query {
    public function __construct(
        protected string $query
    ) {}

    public function execute(PDO $pdo, array $args): mixed {
        if(($stmt = $pdo->prepare($this->query))
                &&$this->bindValues($stmt, $args)) {
            return $stmt->fetchAll();
        }

        return false;
    }

    protected function bindValues(PDOStatement $statement, array $args): bool {
        foreach ($args as $key => $value) {
            $type = PDO::PARAM_STR;
            if(is_int($value)) {
                $type = PDO::PARAM_INT;
            }

            $statement->bindValue($key, $value, $type);
        }

        return true;
    }
}