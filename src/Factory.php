<?php

namespace Mitoop\XCrypto;

use Mitoop\XCrypto\Contracts\EvmTokenInterface;
use Mitoop\XCrypto\Contracts\TokenInterface;
use Mitoop\XCrypto\Exceptions\InvalidArgumentException;

class Factory
{
    /**
     * @param array{
     *      chain: string,
     *      contract: string,
     *      token: string,
     *      token_decimals: int,
     *      contract_address: string,
     *      chain_id: int,
     *      rpc_endpoint: string
     *  } $config Configuration for initializing the token instance.
     *
     * @throws InvalidArgumentException
     */
    public static function create(array $config): TokenInterface|EvmTokenInterface
    {
        $requiredKeys = ['chain', 'contract', 'token', 'token_decimals', 'contract_address', 'chain_id', 'rpc_endpoint'];
        foreach ($requiredKeys as $key) {
            if (empty($config[$key])) {
                $chain = $config['chain'] ?? 'unknown chain';
                throw new InvalidArgumentException("Missing required configuration key: '{$key}' for chain: '{$chain}'.");
            }
        }

        $chain = ucfirst(strtolower($config['chain']));
        $contract = ucfirst(strtolower($config['contract']));
        $token = ucfirst(strtolower($config['token']));

        $class = sprintf('%s\\Tokens\\%s\\%s\\%s', __NAMESPACE__, $chain, $contract, $token);

        if (! class_exists($class)) {
            throw new InvalidArgumentException("Unable to locate the Token API class for token '{$config['token']}' on the '{$config['chain']}' chain.");
        }

        return new $class($config);
    }
}
