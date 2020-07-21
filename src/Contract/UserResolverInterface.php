<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\Contract;

use Cidaas\OpenAuth\Struct\UserStruct;
use Shopware\Core\Framework\Context;

interface UserResolverInterface
{
    public function resolve(UserStruct $user, string $state, string $clientId, Context $context): void;
}
