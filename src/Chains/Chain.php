<?php

namespace Mitoop\XCrypto\Chains;

use Mitoop\XCrypto\Contracts\ChainInterface;
use Mitoop\XCrypto\Exceptions\InvalidArgumentException;
use Mitoop\XCrypto\Support\Http\HttpRequestClient;
use Mitoop\XCrypto\Wallets\Factory;
use Mitoop\XCrypto\Wallets\Wallet;

abstract class Chain implements ChainInterface
{
    use HttpRequestClient;

    public function __construct(protected array $config) {}

    public function config(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function generateWallet(): Wallet
    {
        return Factory::create($this->config('chain'))->generate();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function validateAddress(string $address): bool
    {
        return Factory::create($this->config('chain'))->validate($address);
    }

    public function getGuzzleOptions(): array
    {
        return [
            'base_uri' => $this->config['rpc_endpoint'],
            'timeout' => 60,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ];
    }
}
