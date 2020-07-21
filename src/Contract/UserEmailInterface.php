<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\Contract;

use Shopware\Core\Framework\Context;
use Shopware\Core\System\User\UserCollection;

interface UserEmailInterface
{
    public function add(string $userId, string $email, string $clientId, Context $context): string;

    public function searchUser(array $emails, Context $context): UserCollection;
}
