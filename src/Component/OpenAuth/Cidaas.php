<?php declare(strict_types=1);

namespace WidasCidaasExtension\Component\OpenAuth;

use League\OAuth2\Client\Provider\GenericProvider;

class Cidaas extends GenericProvider
{
    /**
     * @var array|string[]
     */
    protected $scopes;

    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://nightlybuild.cidaas.de/token-srv/token';
    }

    public function getBaseAuthorizationUrl()
    {
        return 'https://nightlybuild.cidaas.de/authz-srv/authz';
    }

    public function getDefaultScopes()
    {
        return $this->scopes;
    }
}

