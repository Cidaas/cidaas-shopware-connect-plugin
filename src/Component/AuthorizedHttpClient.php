<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\Component;

use GuzzleHttp\ClientInterface;
use Cidaas\OpenAuth\Component\Contract\AuthorizedHttpClientInterface;
use Cidaas\OpenAuth\Contract\TokenRefresherInterface;
use Cidaas\OpenAuth\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Tool\RequestFactory;
use Shopware\Core\Framework\Context;

class AuthorizedHttpClient implements AuthorizedHttpClientInterface
{
    /**
     * @var AbstractProvider
     */
    private $oauthProvider;

    /**
     * @var TokenRefresherInterface
     */
    private $tokenRefresher;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $userId;

    /**
     * @var int
     */
    private $secondsValid;

    public function __construct(
        AbstractProvider $oauthProvider,
        TokenRefresherInterface $tokenRefresher,
        Context $context,
        string $clientId,
        string $userId,
        int $secondsValid
    ) {
        $this->oauthProvider = $oauthProvider;
        $this->tokenRefresher = $tokenRefresher;
        $this->context = $context;
        $this->clientId = $clientId;
        $this->userId = $userId;
        $this->secondsValid = $secondsValid;
    }

    public function getClient(): ClientInterface
    {
        return $this->oauthProvider->getHttpClient();
    }

    public function getRequestFactory(): RequestFactory
    {
        return $this->oauthProvider->getRequestFactory();
    }

    public function getHeaders(): array
    {
        $token = $this->tokenRefresher->refresh($this->clientId, $this->userId, $this->secondsValid, $this->context);

        return $this->oauthProvider->getHeaders($token === null ? null : $token->getAccessToken());
    }
}
