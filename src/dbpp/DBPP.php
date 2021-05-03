<?php


namespace dbpp;


use ArrayObject;
use dbpp\attrs\Query;
use PDO;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;

class DBPP {
    public static function init(Database $database, PDO $pdo): void {
        $class = new ReflectionClass($database);
        $properties = $class->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach($properties as $property){
            $database->{$property->getName()} = self::initDao($property->getType()->getName(), $pdo);
        }
    }

    /**
     * @throws ReflectionException
     */
    private static function initDao(string $classname, PDO $pdo): object {
        $class = new ReflectionClass($classname);

        return new $classname(function(string $name, array $arguments) use($class, $pdo) {
            $function = $class->getMethod($name);
            $response = false;

            foreach($function->getAttributes() as $attribute){
                if(class_exists($attribute->getName())){
                    $attribute = $attribute->newInstance();
                    if($attribute instanceof Query) {
                        $args = [];
                        for($i = 0, $iMax = count($arguments); $i < $iMax; $i++) {
                            $args[$function->getParameters()[$i]->getName()] = $arguments[$i];
                        }

                        $response = $attribute->execute($pdo, $args);
                        break;
                    }
                }
            }

            return DBPP::getValueByType($function->getReturnType(), $response);
        });
    }

    public static function getValueByType(ReflectionType $type, mixed $value): mixed{
        if($type instanceof ReflectionNamedType){
            $value = self::getValueByNamedType($type, $value);
            if($value===null){
                if($type->allowsNull()) {
                    return null;
                }
            }else return $value;
        }else if($type instanceof ReflectionUnionType){
            foreach($type->getTypes() as $namedType){
                $val = self::getValueByNamedType($namedType, $value);
                if($val!==null) {
                    return $val;
                }
            }

            if($type->allowsNull()) {
                return null;
            }
        }

        throw new ClassCastException("The function requires dbpp to return string, ".
            "but dbpp cannot do this. ".
            "Add to return type |bool or set return type array|bool");
    }

    /**
     * @throws ReflectionException
     */
    private static function getValueByNamedType(ReflectionNamedType $type, mixed $value): mixed {
        return match($type->getName()){
            "bool" => $value!==false,
            "array" => is_array($value)?$value:null,
            "object" => is_array($value)?new ArrayObject($value):null,
            "mixed" => $value,
            "false" => $value===false?false:null,

            default => null
        };
    }
}