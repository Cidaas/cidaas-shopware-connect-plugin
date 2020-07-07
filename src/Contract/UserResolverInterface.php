<?php declare(strict_types=1);

namespace WidasCidaasExtension\Contract;

use Shopware\Core\Framework\Context;
use WidasCidaasExtension\Struct\UserStruct;

interface UserResolverInterface
{
    public function resolve(UserStruct $user, string $state, string $clientId, Context $context): void;
}