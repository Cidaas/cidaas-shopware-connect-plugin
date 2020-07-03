<?php declare(strict_types=1);

namespace WidasCidaasExtension\Component\Contract;

use League\OAuth2\Client\Provider\AbstractProvider;
use WidasCidaasExtension\Struct\TokenPairStruct;
use WidasCidaasExtension\Struct\UserStruct;

interface ClientInterface
{
    public function getLoginUrl(string $state): string;

    public function getUser(string $state, string $code): UserStruct;

    public function refreshToken(string $refreshToken): TokenPairStruct;

    public function getInnerClient(): AbstractProvider;
}
