<?php

namespace Mitoop\XCrypto\Tokens\Polygon\Polygon;

use Mitoop\XCrypto\Chains\PolygonChain;
use Mitoop\XCrypto\Contracts\EvmTokenInterface;
use Mitoop\XCrypto\Tokens\Traits\EvmLike;

class Usdt extends PolygonChain implements EvmTokenInterface
{
    use EvmLike;
}
