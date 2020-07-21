<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\Service;

use Cidaas\OpenAuth\Contract\ClientFeatureCheckerInterface;
use Cidaas\OpenAuth\Contract\ClientLoaderInterface;
use Cidaas\OpenAuth\Contract\TokenRefresherInterface;
use Cidaas\OpenAuth\Contract\UserTokenInterface;
use Cidaas\OpenAuth\Database\UserTokenEntity;
use Cidaas\OpenAuth\Exception\LoadClientException;
use Cidaas\OpenAuth\Struct\TokenPairStruct;
use Shopware\Core\Framework\Context;

class TokenRefresher implements TokenRefresherInterface
{
    /**
     * @var UserTokenInterface
     */
    private $userToken;

    /**
     * @var ClientLoaderInterface
     */
    private $clientLoader;

    /**
     * @var ClientFeatureCheckerInterface
     */
    private $clientFeatureChecker;

    public function __construct(
        UserTokenInterface $userToken,
        ClientLoaderInterface $clientLoader,
        ClientFeatureCheckerInterface $clientFeatureChecker
    ) {
        $this->userToken = $userToken;
        $this->clientLoader = $clientLoader;
        $this->clientFeatureChecker = $clientFeatureChecker;
    }

    public function refresh(string $clientId, string $userId, int $secondsValid, Context $context): ?TokenPairStruct
    {
        if (!$this->clientFeatureChecker->canStoreUserTokens($clientId, $context)) {
            return null;
        }

        $token = $this->userToken->getToken($clientId, $userId, $context);

        if ($token instanceof UserTokenEntity && !empty($token->getRefreshToken())) {
            if ($token->getExpiresAt() !== null) {
                $now = \date_create();
                $expirationDelta = $token->getExpiresAt()->getTimestamp() - $now->getTimestamp();

                if ($expirationDelta > $secondsValid && $expirationDelta > 0) {
                    return (new TokenPairStruct())
                        ->setAccessToken($token->getAccessToken())
                        ->setExpiresAt($token->getExpiresAt())
                        ->setRefreshToken($token->getRefreshToken());
                }
            }

            try {
                $client = $this->clientLoader->load($clientId, $context);
            } catch (LoadClientException $ignored) {
                return null;
            }

            $tokenPair = $client->refreshToken($token->getRefreshToken());
            $this->userToken->setToken($userId, $clientId, $tokenPair, $context);

            return $tokenPair;
        }

        return null;
    }
}
