<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\Service;

use Cidaas\OpenAuth\Component\AuthorizedHttpClient;
use Cidaas\OpenAuth\Component\Contract\AuthorizedHttpClientInterface;
use Cidaas\OpenAuth\Contract\AuthorizedHttpClientFactoryInterface;
use Cidaas\OpenAuth\Contract\ClientLoaderInterface;
use Cidaas\OpenAuth\Contract\TokenRefresherInterface;
use Shopware\Core\Framework\Context;

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
