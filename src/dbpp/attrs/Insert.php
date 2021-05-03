<?php


namespace dbpp\attrs;

use Attribute;
use JetBrains\PhpStorm\Pure;
use PDO;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

#[Attribute]
class Insert extends Query {
    #[Pure] public function __construct(
        private string $tableName,
        private array $keys = []
    ) {
        parent::__construct("");
    }

    /**
     * @throws ReflectionException
     */
    public function execute(PDO $pdo, array $args): array|false {
        if(count($args)===1){
            $parameter = array_values($args)[0];
            if(is_array($parameter)){
                $response = [];
                foreach($parameter as $item){
                    $response[] = $this->insert($pdo, $item);
                }

                return $response;
            }

            return [$this->insert($pdo, $parameter)];
        }

        return false;
    }

    /**
     * @throws ReflectionException
     */
    private function insert(PDO $pdo, mixed $value): array|bool {
        if(is_object($value)){
            $valueClass = new ReflectionClass($value);

            $table = [];
            $tableParameters = [];

            $parameters = [];
            foreach($valueClass->getProperties(ReflectionProperty::IS_PUBLIC) as $property){
                if(count($property->getAttributes(Ignore::class))===0){
                    $name = $property->getName();

                    $nameAttributes = $property->getAttributes(Parameter::class);
                    if(count($nameAttributes)===1){
                        $nameAttribute = $nameAttributes[0];
                        $name = $nameAttribute->newInstance()->name;
                    }

                    $table[] = $name;
                    $tableParameters[] = ":".$name;
                    $parameters[$name] = $value->{$property->getName()};
                }
            }

            $query = "INSERT INTO $this->tableName(".implode(",", $table).") VALUES("
                .implode(",", $tableParameters).");";

            if(($stmt = $pdo->prepare($query))
                &&$stmt->execute($parameters)) {

                $response = [];
                foreach($this->keys as $key) {
                    $response[] = $pdo->lastInsertId($key);
                }

                return $response;
            }
        }

        return false;
    }
}