<?php

namespace Mitoop\XCrypto\Chains;

use Mitoop\XCrypto\Chains\Traits\EvmLike;
use Mitoop\XCrypto\Contracts\EvmChainInterface;

class EthChain extends Chain implements EvmChainInterface
{
    use EvmLike;
}
