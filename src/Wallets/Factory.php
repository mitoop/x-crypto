<?php

namespace Mitoop\XCrypto\Wallets;

use Mitoop\XCrypto\Exceptions\InvalidArgumentException;

class Factory
{
    /**
     * @throws InvalidArgumentException
     */
    public static function create(string $chain): WalletInterface
    {
        return match (strtolower($chain)) {
            'eth', 'bsc', 'polygon' => new EvmWallet,
            'tron' => new TronWallet,
            default => throw new InvalidArgumentException('Unsupported chain type'),
        };
    }
}
