<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\Component\Contract;

use Cidaas\OpenAuth\Struct\TokenPairStruct;
use Cidaas\OpenAuth\Struct\UserStruct;
use Cidaas\OpenAuth\OAuth2\Client\Provider\AbstractProvider;

interface ClientInterface
{
    public function getLoginUrl(string $state): string;

    public function getUser(string $state, string $code): UserStruct;

    public function refreshToken(string $refreshToken): TokenPairStruct;

    public function getInnerClient(): AbstractProvider;
}
