<?php declare(strict_types=1);

namespace Cidaas\OauthConnect\Database\Client;

use Cidaas\OauthConnect\Database\Token\TokenCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\User\UserCollection;

class ClientEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string|null
     */
    protected $clientId;

    /**
     * @var string|null
     */
    protected $clientSecret;

    /**
     * @var string|null
     */
    protected $redirectUri;

    /**
     * @var string|null
     */
    protected $tokenUrl;

    /**
     * @var string|null
     */
    protected $authorizationUrl;

    /**
     * @var bool|null
     */
    protected $active;

    /**
     * @var UserCollection|null
     */
    protected $users;

    /**
     * @var TokenCollection|null
     */
    protected $tokens;

    /**
     * @return string|null
     */
    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    /**
     * @param string|null $clientId
     */
    public function setClientId(?string $clientId): void
    {
        $this->clientId = $clientId;
    }

    /**
     * @return string|null
     */
    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    /**
     * @param string|null $clientSecret
     */
    public function setClientSecret(?string $clientSecret): void
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return string|null
     */
    public function getRedirectUri(): ?string
    {
        return $this->redirectUri;
    }

    /**
     * @param string|null $redirectUri
     */
    public function setRedirectUri(?string $redirectUri): void
    {
        $this->redirectUri = $redirectUri;
    }

    /**
     * @return string|null
     */
    public function getTokenUrl(): ?string
    {
        return $this->tokenUrl;
    }

    /**
     * @param string|null $tokenUrl
     */
    public function setTokenUrl(?string $tokenUrl): void
    {
        $this->tokenUrl = $tokenUrl;
    }

    /**
     * @return string|null
     */
    public function getAuthorizationUrl(): ?string
    {
        return $this->authorizationUrl;
    }

    /**
     * @param string|null $authorizationUrl
     */
    public function setAuthorizationUrl(?string $authorizationUrl): void
    {
        $this->authorizationUrl = $authorizationUrl;
    }

    /**
     * @return bool|null
     */
    public function getActive(): ?bool
    {
        return $this->active;
    }

    /**
     * @param bool|null $active
     */
    public function setActive(?bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return UserCollection|null
     */
    public function getUsers(): ?UserCollection
    {
        return $this->users;
    }

    /**
     * @param UserCollection|null $users
     */
    public function setUsers(?UserCollection $users): void
    {
        $this->users = $users;
    }

    /**
     * @return TokenCollection|null
     */
    public function getTokens(): ?TokenCollection
    {
        return $this->tokens;
    }

    /**
     * @param TokenCollection|null $tokens
     */
    public function setTokens(?TokenCollection $tokens): void
    {
        $this->tokens = $tokens;
    }

}
