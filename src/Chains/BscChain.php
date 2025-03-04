<?php

namespace Mitoop\XCrypto\Chains;

use Mitoop\XCrypto\Chains\Traits\EvmLike;
use Mitoop\XCrypto\Contracts\EvmChainInterface;

class BscChain extends Chain implements EvmChainInterface
{
    use EvmLike;

    public function supportsEIP1559Transaction(): bool
    {
        return false;
    }
}
