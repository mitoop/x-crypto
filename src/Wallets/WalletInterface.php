<?php

namespace Mitoop\XCrypto\Wallets;

interface WalletInterface
{
    public function generate(): Wallet;

    public function validate(string $address): bool;
}
