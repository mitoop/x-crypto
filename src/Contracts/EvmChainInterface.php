<?php

namespace Mitoop\XCrypto\Contracts;

interface EvmChainInterface extends ChainInterface
{
    public function getNativeTokenDecimal(): int;

    public function getLatestBlockNum(): string;

    public function getNativeBalance(string $address): string;

    public function getGasPrice(): string;

    public function estimateGas(string $fromAddress, string $toAddress, ?string $data = null): string;

    public function getTransactionCount(string $address, string $block = 'latest'): string;

    public function getBaseFeePerGas(): array;

    public function supportsEIP1559Transaction(): bool;

    public function normalizeAddress(string $address): string;

    public function padAddress(string $address): string;

    public function removeTrailingZeros(string $number): string;
}
