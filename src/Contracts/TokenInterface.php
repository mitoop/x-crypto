<?php

namespace Mitoop\XCrypto\Contracts;

use Mitoop\XCrypto\TransactionInfo;

interface TokenInterface extends ChainInterface
{
    public function getTokenBalance(string $address): string;

    public function getTransactions(string $address, array $params = []): array;

    public function getTransactionStatus(string $txId): bool;

    public function getTransaction(string $txId): ?TransactionInfo;

    public function transfer($fromAddress, $fromPrivateKey, $toAddress, $amount, $allowPartial = false): string;

    public function convertAmount($rawAmount, $decimal = null): string;
}
