<?php declare(strict_types=1);

namespace WidasCidaasExtension\Contract;

use Shopware\Core\Framework\Context;

interface ClientFeatureCheckerInterface
{
    public function canLogin(string $clientId, Context $context): bool;

    public function canConnect(string $clientId, Context $context): bool;

    public function canStoreUserTokens(string $clientId, Context $context): bool;
}