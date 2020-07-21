<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\OAuth2\Client\Provider;

use Cidaas\OpenAuth\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Cidaas\OpenAuth\OAuth2\Client\Provider\Exception\CidaasIdentityProviderException;
use Psr\Http\Message\ResponseInterface;

class Cidaas extends AbstractProvider
{
    use ArrayAccessorTrait;
    use BearerAuthorizationTrait;   
    

    /**
     * Check a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  array $data Parsed response data
     *
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            throw CidaasIdentityProviderException::clientException($response, $data);
        } elseif (isset($data['error'])) {
            throw CidaasIdentityProviderException::oauthException($response, $data);
        }
    }

    /**
     * Generate a user array from a successful user details request.
     *
     * @param array $response
     * @param AccessToken $token
     *
     * @return \League\OAuth2\Client\Provider\ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new CidaasResourceOwner($response);
    }    
   

    /**
     * Get access token url to retrieve token
     *
     * @param  array $params
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return '';
    }

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return '';
    }

    /**
     * Get the default scopes used by this provider.
     *
     * This should not be a complete list of all scopes, but the minimum
     * required for the provider user interface!
     *
     * @return array
     */
    protected function getDefaultScopes()
    {
        return ['openid', 'email', 'profile', 'roles'];
    }
    
    /**
     * Get provider url to fetch user details
     *
     * @param AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {        
        return ''; 
        
    }
    
    /**
     * Returns the string that should be used to separate scopes when building
     * the URL for requesting an access token.
     *
     * @return string Scope separator, defaults to ' '
     */
    protected function getScopeSeparator()
    {
        return ' ';
    }    
    
}
