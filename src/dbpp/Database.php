<?php


namespace dbpp;


use PDO;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;

abstract class Database {
    public final function __construct(PDO $pdo) {
        $class = new ReflectionClass($this);
        $properties = $class->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach($properties as $property){
            $propertyType = $property->getType();
            if($propertyType instanceof ReflectionNamedType) {
                try {
                    $class = new ReflectionClass($propertyType->getName());
                    if($class->isSubclassOf(Dao::class)){
                        $this->{$property->getName()} = $class->newInstance($pdo);
                    }
                } catch (ReflectionException) {}
            }
        }
    }
}