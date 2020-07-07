<?php declare(strict_types=1);

namespace WidasCidaasExtension\Contract;

use Shopware\Core\Framework\Context;
use WidasCidaasExtension\Struct\TokenPairStruct;

interface TokenRefresherInterface
{
    public function refresh(string $clientId, string $userId, int $secondsValid, Context $context): ?TokenPairStruct;
}