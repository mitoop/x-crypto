<?php

namespace Mitoop\XCrypto\Chains\Traits;

use Mitoop\XCrypto\Exceptions\RpcException;
use Mitoop\XCrypto\Responses\EvmLikeResponse;

/**
 * @method EvmLikeResponse postJson(string $url, array $data = [], array $options = [])
 */
trait EvmLike
{
    /**
     * @throws RpcException
     */
    public function getChainId(): int
    {
        $response = $this->rpcRequest('eth_chainId');

        return hexdec($response->json('result'));
    }

    public function getNativeTokenDecimal(): int
    {
        return 18;
    }

    /**
     * @throws RpcException
     */
    public function getLatestBlockNum(): string
    {
        $response = $this->rpcRequest('eth_blockNumber');

        // 🌰 "0x2e29731"
        return $response->json('result');
    }

    /**
     * @throws RpcException
     */
    public function getNativeBalance(string $address): string
    {
        $response = $this->rpcRequest('eth_getBalance', [
            $address,
            'latest',
        ]);

        // 🌰 "0x853a0d2313c0000" => "600000000000000000" wei
        return gmp_strval(gmp_init($response->json('result'), 16));
    }

    /**
     * @throws RpcException
     */
    public function getGasPrice(): string
    {
        $response = $this->rpcRequest('eth_gasPrice');

        // 🌰 "0x77359400" => "2000000000" wei
        return gmp_strval(gmp_init($response->json('result'), 16));
    }

    /**
     * @throws RpcException
     */
    public function estimateGas(string $fromAddress, string $toAddress, ?string $data = null): string
    {
        $params = [
            'from' => $fromAddress,
            'to' => $toAddress,
            'block' => 'latest',
        ];

        if (! is_null($data)) {
            $params['data'] = $data;
        }

        $response = $this->rpcRequest('eth_estimateGas', [
            $params,
        ]);

        // 🌰 "0x5208" => "21000" gas
        return gmp_strval(gmp_init($response->json('result'), 16));
    }

    /**
     * @throws RpcException
     */
    public function getTransactionCount(string $address, string $block = 'latest'): string
    {
        $response = $this->rpcRequest('eth_getTransactionCount', [
            $address,
            $block,
        ]);

        // 🌰 "0x1" => "1"
        return gmp_strval(gmp_init($response->json('result'), 16));
    }

    /**
     * @throws RpcException
     */
    public function getBaseFeePerGas(): array
    {
        $response = $this->rpcRequest('eth_feeHistory', [
            1,
            'latest',
            [50],
        ]);

        // 🌰 ["0xc5f55767", "0x3b9aca00"]
        return [$response->json('result.baseFeePerGas.0'), $response->json('result.reward.0.0')];
    }

    public function supportsEIP1559Transaction(): bool
    {
        return true;
    }

    public function normalizeAddress(string $address): string
    {
        return '0x'.substr($address, -40);
    }

    public function padAddress(string $address, $withPrefix = false): string
    {
        $paddedAddress = str_pad(substr($address, 2), 64, '0', STR_PAD_LEFT);

        if ($withPrefix) {
            return '0x'.$paddedAddress;
        }

        return $paddedAddress;
    }

    public function removeTrailingZeros(string $number): string
    {
        return rtrim(rtrim($number, '0'), '.');
    }

    protected function newResponse($response): EvmLikeResponse
    {
        return new EvmLikeResponse($response);
    }

    /**
     * @throws RpcException
     */
    protected function rpcRequest(string $method, array $params = []): EvmLikeResponse
    {
        $response = $this->postJson('', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => $method,
            'params' => $params,
        ]);

        if (! $response->bizOk()) {
            $message = sprintf('%s:%s', $method, $response->getBizErrorMsg());

            throw new RpcException($message);
        }

        return $response;
    }
}
