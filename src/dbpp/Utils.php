<?php


namespace dbpp;

use ArrayObject;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

class Utils {
    /**
     * @throws DBPPException
     */
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

        throw new DBPPException("The function requires dbpp to return string, ".
            "but dbpp cannot do this. ".
            "Add to return type |bool or set return type array|bool");
    }

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