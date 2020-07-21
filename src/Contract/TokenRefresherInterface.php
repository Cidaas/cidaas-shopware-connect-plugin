<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\Contract;

use Cidaas\OpenAuth\Struct\TokenPairStruct;
use Shopware\Core\Framework\Context;

interface TokenRefresherInterface
{
    public function refresh(string $clientId, string $userId, int $secondsValid, Context $context): ?TokenPairStruct;
}
