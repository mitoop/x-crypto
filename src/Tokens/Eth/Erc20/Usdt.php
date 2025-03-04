<?php

namespace Mitoop\XCrypto\Tokens\Eth\Erc20;

use Mitoop\XCrypto\Chains\EthChain;
use Mitoop\XCrypto\Contracts\EvmTokenInterface;
use Mitoop\XCrypto\Tokens\EvmLike;

class Usdt extends EthChain implements EvmTokenInterface
{
    use EvmLike;
}
