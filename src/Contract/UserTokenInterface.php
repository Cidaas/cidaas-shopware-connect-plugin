<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\Contract;

use Cidaas\OpenAuth\Database\UserTokenEntity;
use Cidaas\OpenAuth\Struct\TokenPairStruct;
use Shopware\Core\Framework\Context;

interface UserTokenInterface
{
    public function setToken(string $userId, string $clientId, TokenPairStruct $token, Context $context): string;

    public function getToken(string $clientId, string $userId, Context $context): ?UserTokenEntity;
}
