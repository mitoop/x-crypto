<?php

namespace Mitoop\XCrypto\Chains;

use Mitoop\XCrypto\Chains\Traits\EvmLike;
use Mitoop\XCrypto\Contracts\EvmChainInterface;

class PolygonChain extends Chain implements EvmChainInterface
{
    use EvmLike;
}
