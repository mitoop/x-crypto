<?php

namespace Mitoop\XCrypto;

class TransactionInfo
{
    public function __construct(
        public bool $status,
        public string $hash,
        public string $from,
        public string $to,
        public string $amount,
        public string $fee,
    ) {}
}
