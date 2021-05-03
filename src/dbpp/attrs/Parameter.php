<?php


namespace dbpp\attrs;

use Attribute;

#[Attribute]
class Parameter {
    public function __construct(
        public string $name
    ) {}
}