<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\Component\OpenAuth;

use Cidaas\OpenAuth\OAuth2\Client\Provider\Cidaas;
use League\OAuth2\Client\Token\AccessToken;

class Widas extends Cidaas
{
    /**
     * @var array|string[]
     */
    protected $scopes;

    public function getBaseUrl() {
        return 'https://nightlybuild.cidaas.de';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->getBaseUrl().'/token-srv/token';
    }

    public function getBaseAuthorizationUrl()
    {
        return $this->getBaseUrl().'/authz-srv/authz';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {        
        return $this->getBaseUrl().'/users-srv/userinfo?access_token='; 
        
    }

    protected function getDefaultScopes()
    {
        return $this->scopes;
    }
}
