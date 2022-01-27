<?php


namespace dbpp;


use dbpp\attrs\Query;
use PDO;
use ReflectionClass;
use ReflectionException;

abstract class Dao {
    private ReflectionClass $class;

    final public function __construct(private PDO $pdo) {
        $this->class = new ReflectionClass($this);
    }

    /**
     * @throws DBPPException
     */
    final public function __call(string $name, array $arguments) {
        $response = false;
        try {
            $method = $this->class->getMethod($name);
            if($method->getPrototype()->isAbstract())
                $method = $method->getPrototype();

            foreach($method->getAttributes() as $attribute){
                if(class_exists($attribute->getName())){
                    $attribute = $attribute->newInstance();
                    if($attribute instanceof Query) {
                        $args = [];

                        for($i = 0, $iMax = count($arguments); $i < $iMax; $i++) {
                            $args[$method->getParameters()[$i]->getName()] = $arguments[$i];
                        }

                        $response = $attribute->execute($this->pdo, $args);
                        break;
                    }
                }
            }

        } catch (ReflectionException) {}

        return Utils::getValueByType($method->getReturnType(), $response);
    }
}