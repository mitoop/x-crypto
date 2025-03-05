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
     *      decimal: int,
     *      contract_address: string,
     *      chain_id: int,
     *      rpc_endpoint: string
     *  } $config Configuration for initializing the token instance.
     *
     * @throws InvalidArgumentException
     */
    public static function create(array $config): TokenInterface|EvmTokenInterface
    {
        $requiredKeys = ['chain', 'contract', 'token', 'decimal', 'contract_address', 'chain_id', 'rpc_endpoint'];
        foreach ($requiredKeys as $key) {
            if (empty($config[$key])) {
                throw new InvalidArgumentException(
                    sprintf("Missing '%s' in config for chain: %s.", $key, $config['chain'] ?? 'unknown')
                );
            }
        }

        $chain = ucfirst(strtolower($config['chain']));
        $contract = ucfirst(strtolower($config['contract']));
        $token = ucfirst(strtolower($config['token']));

        $class = sprintf('%s\\Tokens\\%s\\%s\\%s', __NAMESPACE__, $chain, $contract, $token);

        if (! class_exists($class)) {
            throw new InvalidArgumentException(
                sprintf('Token class not found for %s/%s/%s', $chain, $contract, $token)
            );
        }

        return new $class($config);
    }
}
