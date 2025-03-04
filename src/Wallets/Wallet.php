<?php

namespace Mitoop\XCrypto\Wallets;

class Wallet
{
    public function __construct(
        public string $address,
        public string $privateKey,
        public string $publicKey,
        public ?string $hexAddress = null
    ) {}
}
