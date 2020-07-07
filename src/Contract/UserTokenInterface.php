<?php declare(strict_types=1);

namespace WidasCidaasExtension\Contract;

use Shopware\Core\Framework\Context;
use WidasCidaasExtension\Database\UserTokenEntity;
use WidasCidaasExtension\Struct\TokenPairStruct;

interface UserTokenInterface
{
    public function setToken(string $userId, string $clientId, TokenPairStruct $token, Context $context): string;

    public function getToken(string $clientId, string $userId, Context $context): ?UserTokenEntity;
}