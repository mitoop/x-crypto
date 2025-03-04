<?php

namespace Mitoop\XCrypto\Tokens\Bsc\Bep20;

use Mitoop\XCrypto\Chains\BscChain;
use Mitoop\XCrypto\Contracts\EvmTokenInterface;
use Mitoop\XCrypto\Tokens\EvmLike;

class Usdt extends BscChain implements EvmTokenInterface
{
    use EvmLike;
}
