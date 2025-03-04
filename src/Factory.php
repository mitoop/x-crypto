<?php

namespace Mitoop\XCrypto;

use Mitoop\XCrypto\Contracts\TokenInterface;

class Factory
{
    public static function create(array $config): TokenInterface {}
}
