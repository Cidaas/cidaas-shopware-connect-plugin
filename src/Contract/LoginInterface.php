<?php declare(strict_types=1);

namespace WidasCidaasExtension\Contract;

use Shopware\Core\Framework\Context;
use WidasCidaasExtension\Database\LoginEntity;

interface LoginInterface
{
    public function initiate(string $clientId, ?string $userId, string $state, Context $context): string;

    public function setCredentials(string $state, string $userId, Context $context): bool;

    public function pop(string $state, Context $context): ?LoginEntity;

    public function getUser(string $state, Context $context): ?string;
}