<?php

namespace Mitoop\XCrypto\Responses;

use Mitoop\XCrypto\Support\Http\Response;

class EvmLikeResponse extends Response
{
    public function bizOk(): bool
    {
        return $this->ok() && is_null($this->json('error'));
    }

    public function getBizErrorMsg(): string
    {
        return sprintf('%s:%s', $this->json('error.code'), $this->json('error.message'));
    }
}
