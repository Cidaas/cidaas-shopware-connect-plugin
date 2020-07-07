<?php declare(strict_types=1);

namespace WidasCidaasExtension\Component\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use WidasCidaasExtension\Component\Contract\ClientInterface;
use WidasCidaasExtension\Component\OpenAuth\Cidaas;
use WidasCidaasExtension\Contract\TokenPairFactoryInterface;
use WidasCidaasExtension\Struct\TokenPairStruct;
use WidasCidaasExtension\Struct\UserStruct;

class CidaasClient implements ClientInterface
{
    /**
     * @var TokenPairFactoryInterface
     */
    private $tokenPairFactory;

    /**
     * @var Cidaas
     */
    private $cidaasClient;

    public function __construct(TokenPairFactoryInterface $tokenPairFactory, array  $options)
    {
        $this->tokenPairFactory = $tokenPairFactory;
        $this->cidaasClient = new Cidaas($options);
    }

    public function getLoginUrl(string $state): string
    {
        return $this->cidaasClient->getAuthorizationUrl(['state' => $state]);
    }

    public function getUser(string $state, string $code): UserStruct
    {
        $token = $this->cidaasClient->getAccessToken('authorization_code', ['code' => $code]);
        /** @var  GenericResourceOwner $user*/
        $user = $this->cidaasClient->getResourceOwner($token);

        return (new UserStruct())
            ->setPrimaryKey($user->getId())
            ->setTokenPair($this->tokenPairFactory->fromLeagueToken($token))
            ->setDisplayName($user->toArray()['username'])
            ->setPrimaryEmail($user->toArray()['email'])
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