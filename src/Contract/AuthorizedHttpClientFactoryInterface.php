<?php declare(strict_types=1);

namespace WidasCidaasExtension\Contract;


use Shopware\Core\Framework\Context;
use WidasCidaasExtension\Component\Contract\AuthorizedHttpClientInterface;

interface AuthorizedHttpClientFactoryInterface
{
    public function createAuthorizedHttpClient(
        string $clientId,
        string $userId,
        Context $context
    ): AuthorizedHttpClientInterface;
}
