<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\Component\Provider;

use Cidaas\OpenAuth\Component\Contract\ClientInterface;
use Cidaas\OpenAuth\Component\OpenAuth\Widas;
use Cidaas\OpenAuth\Contract\TokenPairFactoryInterface;
use Cidaas\OpenAuth\Struct\TokenPairStruct;
use Cidaas\OpenAuth\Struct\UserStruct;
use Cidaas\OpenAuth\OAuth2\Client\Provider\AbstractProvider;
use Cidaas\OpenAuth\OAuth2\Client\Provider\CidaasResourceOwner;

class CidaasClient implements ClientInterface
{
    /**
     * @var TokenPairFactoryInterface
     */
    private $tokenPairFactory;

    /**
     * @var Widas
     */
    private $cidaasClient;

    public function __construct(TokenPairFactoryInterface $tokenPairFactory, array $options)
    {
        $this->tokenPairFactory = $tokenPairFactory;
        $this->cidaasClient = new Widas($options);
    }

    public function getLoginUrl(string $state): string
    {
        return $this->cidaasClient->getAuthorizationUrl(['state' => $state]);
    }

    public function getUser(string $state, string $code): UserStruct
    {
        $token = $this->cidaasClient->getAccessToken('authorization_code', ['code' => $code]);
        /** @var CidaasResourceOwner $user */
        $user = $this->cidaasClient->getResourceOwner($token);

        return (new UserStruct())
            ->setPrimaryKey($user->getId())
            ->setTokenPair($this->tokenPairFactory->fromLeagueToken($token))
            ->setDisplayName($user->getName())
            ->setPrimaryEmail($user->getEmail())
            ->setEmails([]);
    }

    public function refreshToken(string $refreshToken): TokenPairStruct
    {
        return $this->tokenPairFactory->fromLeagueToken($this->cidaasClient->getAccessToken('refresh_token', [
            'refresh_token' => $refreshToken,
        ]));
    }

    public function getInnerClient(): AbstractProvider
    {
        return $this->cidaasClient;
    }
}
