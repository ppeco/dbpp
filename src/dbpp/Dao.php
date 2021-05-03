<?php


namespace dbpp;


use Closure;

abstract class Dao {
    final public function __construct(private Closure $onCall) {}

    final public function __call(string $name, array $arguments) {
        return $this->onCall->call($this, $name, $arguments);
    }
}