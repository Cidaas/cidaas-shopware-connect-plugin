<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\Contract;

use Cidaas\OpenAuth\Struct\TokenPairStruct;
use League\OAuth2\Client\Token\AccessTokenInterface;

interface TokenPairFactoryInterface
{
    public function fromLeagueToken(AccessTokenInterface $token): TokenPairStruct;
}
