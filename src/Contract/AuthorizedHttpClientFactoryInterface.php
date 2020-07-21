<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\Contract;

use Cidaas\OpenAuth\Component\Contract\AuthorizedHttpClientInterface;
use Shopware\Core\Framework\Context;

interface AuthorizedHttpClientFactoryInterface
{
    public function createAuthorizedHttpClient(
        string $clientId,
        string $userId,
        Context $context
    ): AuthorizedHttpClientInterface;
}
