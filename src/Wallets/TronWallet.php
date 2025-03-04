<?php

namespace Mitoop\XCrypto\Wallets;

class TronWallet implements WalletInterface
{
    public function generate(): Wallet {}

    public function validate(string $address): bool {}
}
