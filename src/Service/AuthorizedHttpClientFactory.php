<?php declare(strict_types=1);

namespace WidasCidaasExtension\Service;

use Shopware\Core\Framework\Context;
use WidasCidaasExtension\Component\AuthorizedHttpClient;
use WidasCidaasExtension\Component\Contract\AuthorizedHttpClientInterface;
use WidasCidaasExtension\Contract\AuthorizedHttpClientFactoryInterface;
use WidasCidaasExtension\Contract\ClientLoaderInterface;
use WidasCidaasExtension\Contract\TokenRefresherInterface;

class AuthorizedHttpClientFactory implements AuthorizedHttpClientFactoryInterface
{
    /**
     * @var ClientLoaderInterface
     */
    private $clientLoader;

    /**
     * @var TokenRefresherInterface
     */
    private $tokenRefresher;

    public function __construct(ClientLoaderInterface $clientLoader, TokenRefresherInterface $tokenRefresher)
    {
        $this->clientLoader = $clientLoader;
        $this->tokenRefresher = $tokenRefresher;
    }

    public function createAuthorizedHttpClient(
        string $clientId,
        string $userId,
        Context $context
    ): AuthorizedHttpClientInterface {
        $provider = $this->clientLoader->load($clientId, $context);

        return new AuthorizedHttpClient(
            $provider->getInnerClient(),
            $this->tokenRefresher,
            $context,
            $clientId,
            $userId,
            15
        );
    }
}
