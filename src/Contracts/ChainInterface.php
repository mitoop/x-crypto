<?php

namespace Mitoop\Crypto\Contracts;

use Mitoop\Crypto\Support\Http\BizResponseInterface;
use Mitoop\Crypto\Support\Http\HttpMethod;
use Mitoop\Crypto\Wallets\Wallet;

interface ChainInterface
{
    public function generateWallet(): Wallet;

    public function validateAddress(string $address): bool;

    public function getChainId(bool $preferLocal = true): int;

    public function getNativeCoinDecimals(): int;

    public function rpcRequest(string $method, array $params = [], HttpMethod $httpMethod = HttpMethod::POST): BizResponseInterface;
}
