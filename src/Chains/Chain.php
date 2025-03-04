<?php

namespace Mitoop\XCrypto\Chains;

use Mitoop\XCrypto\Contracts\ChainInterface;
use Mitoop\XCrypto\Support\Http\HttpRequestClient;

abstract class Chain implements ChainInterface
{
    use HttpRequestClient;

    public function __construct(protected array $config) {}

    public function config(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
}
