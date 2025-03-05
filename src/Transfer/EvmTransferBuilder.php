<?php

namespace Mitoop\XCrypto\Transfer;

use kornrunner\Ethereum\Token;

class EvmTransferBuilder extends Token
{
    public function encodeAbi(string $toAddress, $amount): string
    {
        $methodId = '0xa9059cbb';
        $addressPadded = str_pad(substr($toAddress, 2), 64, '0', STR_PAD_LEFT);
        $amountHex = str_pad(gmp_strval(gmp_init($amount, 10), 16), 64, '0', STR_PAD_LEFT);

        return $methodId.$addressPadded.$amountHex;
    }
}
