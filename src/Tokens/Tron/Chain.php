<?php

namespace Mitoop\Crypto\Tokens\Tron;

use Mitoop\Crypto\Concerns\Chain\AbstractChain;
use Mitoop\Crypto\Concerns\Tvm\AddressFormatter;
use Mitoop\Crypto\Exceptions\RpcException;
use Mitoop\Crypto\Support\Http\BizResponseInterface;
use Mitoop\Crypto\Support\Http\HttpMethod;
use Mitoop\Crypto\Support\Http\TronResponse;

class Chain extends AbstractChain
{
    use AddressFormatter;

    public function getChainId(bool $preferLocal = true): int
    {
        return 0;
    }

    public function getNativeCoinDecimals(): int
    {
        return 6;
    }

    /**
     * @throws RpcException
     */
    public function rpcRequest(string $method, array $params = [], HttpMethod $httpMethod = HttpMethod::POST): BizResponseInterface
    {
        $response = match ($httpMethod) {
            HttpMethod::POST => $this->postJson($method, $params),
            HttpMethod::GET => $this->getQuery($method, $params),
        };

        if (! $response->bizOk()) {
            $message = sprintf('%s:%s', $method, $response->getBizErrorMsg());

            throw new RpcException($message);
        }

        return $response;
    }

    protected function getGuzzleOptions(): array
    {
        return [
            'base_uri' => $this->config('rpc_url'),
            'timeout' => 60,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'TRON-PRO-API-KEY' => $this->config('rpc_api_key'),
            ],
        ];
    }

    protected function newResponse($response): TronResponse
    {
        return new TronResponse($response);
    }
}
