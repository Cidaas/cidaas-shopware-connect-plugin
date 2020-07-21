<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\OpenAuth;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

class OneTimeTokenScopeRepository implements ScopeRepositoryInterface
{
    /**
     * @var ScopeRepositoryInterface
     */
    private $decorated;

    public function __construct(ScopeRepositoryInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function getScopeEntityByIdentifier($identifier)
    {
        return $this->decorated->getScopeEntityByIdentifier($identifier);
    }

    public function finalizeScopes(array $scopes, $grantType, ClientEntityInterface $clientEntity, $userIdentifier = null)
    {
        if ($grantType === 'cidaas_open_auth_one_time_token') {
            $grantType = 'password';
        }

        return $this->decorated->finalizeScopes($scopes, $grantType, $clientEntity);
    }
}
