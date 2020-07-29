<?php


namespace Cidaas\OauthConnect\Oauth\Provider;

use Cidaas\OauthConnect\Oauth\Adapter\OAuth2;
use Cidaas\OauthConnect\Oauth\Exception\UnexpectedApiResponseException;
use Cidaas\OauthConnect\Oauth\Data;
use Cidaas\OauthConnect\Oauth\User;


class Cidaas extends OAuth2
{
    /**
    * {@inheritdoc}
    */
    public $scope = 'email openid profile roles';

    /**
    * {@inheritdoc}
    */
    protected $apiBaseUrl = 'https://nightlybuild.cidaas.de/';

    /**
    * {@inheritdoc}
    */
    protected $authorizeUrl = 'https://nightlybuild.cidaas.de/authz-srv/authz';

    /**
    * {@inheritdoc}
    */
    protected $accessTokenUrl = 'https://nightlybuild.cidaas.de/token-srv/token';

    /**
    * {@inheritdoc}
    */
    protected $apiDocumentation = 'https://docs.cidaas.de';

    /**
    * {@inheritdoc}
    */
    protected function initialize()
    {
        parent::initialize();

        $this->AuthorizeUrlParameters += [
            'access_type' => 'offline'
        ];

        $this->tokenRefreshParameters += [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret
        ];
    }

    /**
    * {@inheritdoc}
    *
    */
    public function getUserProfile()
    {
        $response = $this->apiRequest('users-srv/userinfo');

        $data = new Data\Collection($response);

        if (! $data->exists('sub')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $userProfile->identifier  = $data->get('sub');
        $userProfile->firstName   = $data->get('given_name');
        $userProfile->lastName    = $data->get('family_name');
        $userProfile->displayName = $data->get('name');
        $userProfile->photoURL    = $data->get('picture');
        $userProfile->profileURL  = $data->get('profile');
        $userProfile->gender      = $data->get('gender');
        $userProfile->language    = $data->get('locale');
        $userProfile->email       = $data->get('email');

        $userProfile->emailVerified = $data->get('email_verified') ? $userProfile->email : '';

        if ($this->config->get('photo_size')) {
            $userProfile->photoURL .= '?sz=' . $this->config->get('photo_size');
        }

        return $userProfile;
    }
   
}
