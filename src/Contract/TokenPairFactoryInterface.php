<?php declare(strict_types=1);

namespace WidasCidaasExtension\Contract;

use League\OAuth2\Client\Token\AccessTokenInterface;
use WidasCidaasExtension\Struct\TokenPairStruct;

interface TokenPairFactoryInterface
{
    public function fromLeagueToken(AccessTokenInterface $token): TokenPairStruct;
}