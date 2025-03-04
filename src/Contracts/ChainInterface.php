<?php

namespace Mitoop\XCrypto\Contracts;

use Mitoop\XCrypto\Wallets\Wallet;

interface ChainInterface
{
    public function getChainId(): int;

    public function generateWallet(): Wallet;

    public function validateAddress(string $address): bool;
}
