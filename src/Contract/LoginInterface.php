<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\Contract;

use Cidaas\OpenAuth\Database\LoginEntity;
use Shopware\Core\Framework\Context;

interface LoginInterface
{
    public function initiate(string $clientId, ?string $userId, string $state, Context $context): string;

    public function setCredentials(string $state, string $userId, Context $context): bool;

    public function pop(string $state, Context $context): ?LoginEntity;

    public function getUser(string $state, Context $context): ?string;
}
