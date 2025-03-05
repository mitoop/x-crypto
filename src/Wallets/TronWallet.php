<?php

namespace Mitoop\XCrypto\Wallets;

use Elliptic\EC;
use kornrunner\Keccak;
use StephenHill\Base58;

class TronWallet implements WalletInterface
{
    public function generate(): Wallet
    {
        $ec = new EC('secp256k1');
        $key = $ec->genKeyPair();

        $privateKey = $key->getPrivate('hex');
        $publicKey = $key->getPublic('hex');

        $hexAddress = $this->generateHexAddress($publicKey);
        $address = $this->convertHexToBase58($hexAddress);

        return new Wallet($address, $privateKey, $publicKey, $hexAddress);
    }

    protected function generateHexAddress(string $publicKey): string
    {
        $hash = Keccak::hash(substr(hex2bin($publicKey), -64), 256);

        return '41'.substr($hash, 24);
    }

    protected function convertHexToBase58(string $hexString): string
    {
        $address = hex2bin($hexString);
        $checksum = substr(hash('sha256', hex2bin(hash('sha256', $address))), 0, 8);
        $address .= hex2bin($checksum);

        return (new Base58)->encode($address);
    }

    public function validate(string $address): bool {}
}
