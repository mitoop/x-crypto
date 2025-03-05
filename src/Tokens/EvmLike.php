<?php

namespace Mitoop\XCrypto\Tokens;

use Mitoop\XCrypto\Exceptions\AmountTooSmallException;
use Mitoop\XCrypto\Exceptions\BalanceShortageException;
use Mitoop\XCrypto\Exceptions\GasShortageException;
use Mitoop\XCrypto\Exceptions\RpcException;
use Mitoop\XCrypto\Transaction;
use Mitoop\XCrypto\TransactionInfo;
use Mitoop\XCrypto\Transfer\EvmEIP1559Transaction;
use Mitoop\XCrypto\Transfer\EvmLegacyTransaction;
use Mitoop\XCrypto\Transfer\EvmTransferBuilder;

trait EvmLike
{
    /**
     * @throws RpcException
     */
    public function getTokenBalance(string $address): string
    {
        $methodId = '0x70a08231';
        $addressPadded = $this->padAddress($address);

        $data = $methodId.$addressPadded;

        $response = $this->rpcRequest('eth_call', [
            [
                'to' => $this->config('contract_address'),
                'data' => $data,
            ],
            'latest',
        ]);

        return $this->removeTrailingZeros($this->convertAmount($response->json('result')));
    }

    public function convertAmount($rawAmount, $decimal = null): string
    {
        $decimal = $decimal ?? $this->config('decimal');

        $amount = gmp_strval(gmp_init($rawAmount, 16));

        return bcdiv($amount, bcpow(10, (string) $decimal), $decimal);
    }

    /**
     * @param array{
     *     latest_block_num: string,
     * } $params
     *
     * @throws RpcException
     *
     * @descriptiopn 🌰 $response->json('result')
     * [
     *     0 => array:9 [
     *         "address" => "0x0fd9e8d3af1aaee056eb9e802c3a762a667b1904"
     *         "blockHash" => "0xf35c1387d8513e009339d0da1d617e0a5c3bf9c66e49c025d3aebb7ee33a0236"
     *         "blockNumber" => "0x11df0b2"
     *         "data" => "0x0000000000000000000000000000000000000000000000015af1d78b58c40000"
     *         "logIndex" => "0x0"
     *         "removed" => false
     *         "topics" => array:3 [
     *             0 => "0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef"
     *             1 => "0x0000000000000000000000004281ecf07378ee595c564a59048801330f3084ee"
     *             2 => "0x0000000000000000000000003c99992daa67403a03ba18ad2f36e344ce0a6bfa"
     *         ]
     *         "transactionHash" => "0xdc31e44c6e1deb917f2abfc71b1ee1ade7d48d49cf4599b187c70e6fb27448eb"
     *         "transactionIndex" => "0x0"
     *     ]
     * ]
     */
    public function getTransactions($address, array $params = []): array
    {
        $topic0 = '0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef';
        $topic2 = $this->padAddress($address, true);

        $response = $this->rpcRequest('eth_getLogs', [
            [
                'fromBlock' => $params['latest_block_num'] ?? '0x0', // 十六进制如 "0x2e2a650"
                'toBlock' => 'latest',
                'address' => $this->config('contract_address'),
                'topics' => [$topic0, null, $topic2],
            ],
        ]);

        $transactions = [];
        foreach ($response->json('result') as $item) {
            if ($item['removed']) {
                continue;
            }

            $transactions[] = new Transaction(
                $item['transactionHash'],
                $item['address'],
                $this->normalizeAddress($item['topics'][1]),
                $this->normalizeAddress($item['topics'][2]),
                $item['data'],
                $this->removeTrailingZeros($this->convertAmount($item['data'])),
                $this->config('decimal'),
            );
        }

        return $transactions;
    }

    /**
     * @throws RpcException
     */
    public function getTransactionStatus(string $txId): bool
    {
        $response = $this->rpcRequest('eth_getTransactionReceipt', [
            $txId,
        ]);

        $result = $response->json('result');

        if ($result === null) {
            return false;
        }

        return hexdec($response->json('result.status', 0)) === 1;
    }

    /**
     * @throws RpcException
     *
     * @descriptiopn 🌰 $response->json('result')
     * [
     *     "blockHash" => "0xf35c1387d8513e009339d0da1d617e0a5c3bf9c66e49c025d3aebb7ee33a0236"
     *     "blockNumber" => "0x11df0b2"
     *     "contractAddress" => null
     *     "cumulativeGasUsed" => "0xc918"
     *     "effectiveGasPrice" => "0xdf8475800"
     *     "from" => "0x4281ecf07378ee595c564a59048801330f3084ee"
     *     "gasUsed" => "0xc918"
     *     "logs" => array:2 [
     *         0 => array:9 [
     *             "address" => "0x0fd9e8d3af1aaee056eb9e802c3a762a667b1904"
     *             "blockHash" => "0xf35c1387d8513e009339d0da1d617e0a5c3bf9c66e49c025d3aebb7ee33a0236"
     *             "blockNumber" => "0x11df0b2"
     *             "data" => "0x0000000000000000000000000000000000000000000000015af1d78b58c40000"
     *             "logIndex" => "0x0"
     *             "removed" => false
     *             "topics" => array:3 [
     *                  0 => "0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef"
     *                  1 => "0x0000000000000000000000004281ecf07378ee595c564a59048801330f3084ee"
     *                  2 => "0x0000000000000000000000003c99992daa67403a03ba18ad2f36e344ce0a6bfa"
     *             ]
     *             "transactionHash" => "0xdc31e44c6e1deb917f2abfc71b1ee1ade7d48d49cf4599b187c70e6fb27448eb"
     *             "transactionIndex" => "0x0"
     *         ]
     *         1 => array:9 [
     *             "address" => "0x0000000000000000000000000000000000001010"
     *             "blockHash" => "0xf35c1387d8513e009339d0da1d617e0a5c3bf9c66e49c025d3aebb7ee33a0236"
     *             "blockNumber" => "0x11df0b2"
     *             "data" => "0x000000000000000000000000000000000000000000000000000af93f4abc7798000000000000000000000000000000000000000000000c98d87ab46568e1920e00000000000000000000000000000000000000000000062878025211ebe643bf000000000000000000000000000000000000000000000c98d86fbb261e251a76000000000000000000000000000000000000000000000628780d4b5136a2bb57"
     *             "logIndex" => "0x1"
     *             "removed" => false
     *             "topics" => array:4 [
     *                 0 => "0x4dfe1bbbcf077ddc3e01291eea2d5c70c2b422b415d95645b9adcfd678cb1d63"
     *                 1 => "0x0000000000000000000000000000000000000000000000000000000000001010"
     *                 2 => "0x0000000000000000000000004281ecf07378ee595c564a59048801330f3084ee"
     *                 3 => "0x0000000000000000000000006dc2dd54f24979ec26212794c71afefed722280c"
     *             ]
     *             "transactionHash" => "0xdc31e44c6e1deb917f2abfc71b1ee1ade7d48d49cf4599b187c70e6fb27448eb"
     *             "transactionIndex" => "0x0"
     *         ]
     *     ]
     *     "logsBloom" => "0x00000000000000000000000000000000000000000008100000000000000000000000000000000000000000000000000000008000000000000000021000000000000000000000000000000008000000800000004000000000000100080000000000000000000000000000000000000000000000000000000080000010000000020000000000020000000000000040000000000000000000000000000000000000200000000000000000000000001000000000000000000000000000000000004000000002000000000001000000000000000000000000000000100200000000000000000000400000000000000000000000000000000000000000000000100000"
     *     "status" => "0x1"
     *     "to" => "0x0fd9e8d3af1aaee056eb9e802c3a762a667b1904"
     *     "transactionHash" => "0xdc31e44c6e1deb917f2abfc71b1ee1ade7d48d49cf4599b187c70e6fb27448eb"
     *     "transactionIndex" => "0x0"
     *     "type" => "0x2"
     * ]
     */
    public function getTransaction(string $txId): ?TransactionInfo
    {
        $response = $this->rpcRequest('eth_getTransactionReceipt', [
            $txId,
        ]);

        $result = $response->json('result');

        if ($result === null) {
            return null;
        }

        $status = hexdec($response->json('result.status', 0)) === 1;

        if (! $status) {
            return null;
        }

        $amount = 0;
        $from = '';
        $to = '';
        $logs = $response->json('result.logs', []);
        foreach ($logs as $log) {
            if (! empty($log['topics'][0])
                &&
                $log['topics'][0] === '0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef'
                &&
                strtolower($log['address']) === strtolower($this->config('contract_address'))
                &&
                ! $log['removed']
            ) {
                $amount = $this->convertAmount($log['data'] ?? '0x0');
                $from = $this->normalizeAddress($log['topics'][1]);
                $to = $this->normalizeAddress($log['topics'][2]);
                break;
            }
        }

        $fee = bcmul(
            gmp_strval(gmp_init($response->json('result.effectiveGasPrice'), 16)),
            gmp_strval(gmp_init($response->json('result.gasUsed'), 16)),
            0
        );

        $fee = bcdiv($fee, bcpow(10, $this->getNativeTokenDecimal()), $this->getNativeTokenDecimal());

        $fee = $this->removeTrailingZeros($fee);

        return new TransactionInfo(
            true,
            (string) $response->json('result.transactionHash'),
            $from,
            $to,
            $this->removeTrailingZeros((string) $amount),
            $fee,
        );
    }

    /**
     * @throws RpcException
     * @throws GasShortageException
     * @throws BalanceShortageException
     * @throws AmountTooSmallException
     */
    public function transfer($fromAddress, $fromPrivateKey, $toAddress, $amount, $allowPartial = false): string
    {
        if ($this->isBelowMinimumAmount($amount)) {
            throw new AmountTooSmallException(sprintf('amount: %s', $amount));
        }

        $balance = $this->getTokenBalance($fromAddress);

        if (bccomp($balance, $amount, $this->config('decimal')) === -1) {
            if (! $allowPartial) {
                throw new BalanceShortageException(sprintf('balance: %s, amount: %s', $balance, $amount));
            }

            if ($this->isBelowMinimumAmount($balance)) {
                throw new BalanceShortageException(sprintf('balance: %s', $balance));
            }

            $amount = $balance;
        }

        $transferBuilder = new EvmTransferBuilder;
        $gasPrice = $this->getGasPrice();
        $estimatedGas = $this->estimateGas($fromAddress, $this->config('contract_address'), $transferBuilder->encodeAbi($toAddress, $amount));
        $gasLimit = bcmul($estimatedGas, 1.2);
        $gasLimit = bcdiv($gasLimit, '1', 0);
        $gasLimit = '0x'.gmp_strval(gmp_init($gasLimit, 10), 16);
        $nativeBalance = $this->getNativeBalance($fromAddress);

        if (bccomp($nativeBalance, $fee = bcmul($gasPrice, $estimatedGas)) < 0) {
            throw new GasShortageException(sprintf('balance: %s, fee: %s', $nativeBalance, $fee));
        }

        $nonce = $this->getTransactionCount($fromAddress);
        $hexAmount = $transferBuilder->hexAmount($amount, $this->config('decimal'));
        $data = $transferBuilder->getTransferData($toAddress, $hexAmount);

        if (! $this->supportsEIP1559Transaction()) {
            return $this->createLegacyTransaction($fromPrivateKey, $nonce, $gasPrice, $gasLimit, $data);
        }

        return $this->createEIP1559Transaction($fromPrivateKey, $nonce, $gasLimit, $data);
    }

    /**
     * @throws RpcException
     */
    protected function createLegacyTransaction($fromPrivateKey, $nonce, $gasPrice, $gasLimit, $data): string
    {
        $transaction = new EvmLegacyTransaction($nonce, $gasPrice, $gasLimit, $this->config('contract_address'), data: $data);

        $response = $this->rpcRequest('eth_sendRawTransaction', [
            '0x'.$transaction->getRaw($fromPrivateKey, $this->config('chain_id')),
        ]);

        // 🌰 $response->json('result') => "0x0f09e12c4c3dbfcad9bc71c3c73adb0c00c2a13bf9f5e04366c841ee9f61fb5e"

        return $response->json('result');
    }

    /**
     * @throws RpcException
     */
    protected function createEIP1559Transaction($fromPrivateKey, $nonce, $gasLimit, $data): string
    {
        [$baseFeePerGas, $maxPriorityFeePerGas] = $this->getBaseFeePerGas();
        $baseFeeWei = gmp_strval(gmp_init($baseFeePerGas, 16));
        $priorityFeeWei = gmp_strval(gmp_init($maxPriorityFeePerGas, 16));
        $totalFeeWei = bcadd($baseFeeWei, $priorityFeeWei);
        $maxFeeWei = bcmul($totalFeeWei, '1.2', 0);
        $maxFeePerGas = '0x'.gmp_strval(gmp_init($maxFeeWei, 10), 16);

        $transaction = new EvmEIP1559Transaction(
            $nonce,
            $maxPriorityFeePerGas,
            $maxFeePerGas,
            $gasLimit,
            $this->config('contract_address'),
            data: $data);

        $response = $this->rpcRequest('eth_sendRawTransaction', [
            '0x'.$transaction->getRaw($fromPrivateKey, $this->config('chain_id')),
        ]);

        // 🌰 $response->json('result') => "0x5ec2cfcec7693750992a26f07b4eaa7d3fc792021d105dfdbf78989c9d4df18a"

        return $response->json('result');
    }

    protected function isBelowMinimumAmount(string $amount): bool
    {
        return bccomp($amount, '0.01', $this->config('decimal')) <= 0;
    }
}
