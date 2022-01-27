<?php


namespace dbpp;


use PDO;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
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
                        $this->{$property->getName()} = $this->createDao($pdo, $class);
                    }
                } catch (ReflectionException) {}
            }
        }
    }

    private function createDao(PDO $pdo, ReflectionClass $class): Dao {
        $tempName = $class->name."Impl".time();
        $abstractMethods = $class->getMethods(ReflectionMethod::IS_ABSTRACT);
        $classDef = "class $tempName extends $class->name{";
        foreach($abstractMethods as $method) {
            $argsDef = [];
            $argsDefArray = [];
            foreach ($method->getParameters() as $parameter) {
                $argsDef[] = ($parameter->getType() ?: "") . "$" .$parameter->name;
                $argsDefArray[] = "\"$parameter->name\"=>\$$parameter->name";
            }

            $classDef .= "public function $method->name(".implode(",", $argsDef)."):{$method->getReturnType()}{".
                "return \$this->__call(\"$method->name\", [".implode(",", $argsDefArray)."]);}";
        }

        $classDef .= "}";
        eval($classDef);

        return new $tempName($pdo);
    }
}