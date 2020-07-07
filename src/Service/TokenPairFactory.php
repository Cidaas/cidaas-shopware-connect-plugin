<?php declare(strict_types=1);

namespace WidasCidaasExtension\Service;

use WidasCidaasExtension\Contract\TokenPairFactoryInterface;
use WidasCidaasExtension\Struct\TokenPairStruct;
use League\OAuth2\Client\Token\AccessTokenInterface;

class TokenPairFactory implements TokenPairFactoryInterface
{
    public function fromLeagueToken(AccessTokenInterface $token): TokenPairStruct
    {
        return (new TokenPairStruct())
            ->setAccessToken($token->getToken())
            ->setRefreshToken($token->getRefreshToken())
            ->setExpiresAt($token->getExpires() ? \date_create()->setTimestamp($token->getExpires()) : null);
    }
}
