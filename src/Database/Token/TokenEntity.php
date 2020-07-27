<?php declare(strict_types=1);

namespace Cidaas\OauthConnect\Database\Token;

use Cidaas\OauthConnect\Database\Client\ClientEntity;
use DateTimeInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\User\UserEntity;

class TokenEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string|null
     */
    protected $userId;

    /**
     * @var string|null
     */
    protected $clientId;

    /**
     * @var string|null
     */
    protected $refreshToken;

    /**
     * @var string|null
     */
    protected  $accessToken;

    /**
     * @var DateTimeInterface|null
     */
    protected $expiresAt;

    /**
     * @var ClientEntity|null
     */
    protected $client;

    /**
     * @var UserEntity|null
     */
    protected $user;

    /**
     * @return string|null
     */
    public function getUserId(): ?string
    {
        return $this->userId;
    }

    /**
     * @param string|null $userId
     */
    public function setUserId(?string $userId): void
    {
        $this->userId = $userId;
    }

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
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    /**
     * @param string|null $refreshToken
     */
    public function setRefreshToken(?string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }

    /**
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * @param string|null $accessToken
     */
    public function setAccessToken(?string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getExpiresAt(): ?DateTimeInterface
    {
        return $this->expiresAt;
    }

    /**
     * @param DateTimeInterface|null $expiresAt
     */
    public function setExpiresAt(?DateTimeInterface $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }

    /**
     * @return ClientEntity|null
     */
    public function getClient(): ?ClientEntity
    {
        return $this->client;
    }

    /**
     * @param ClientEntity|null $client
     */
    public function setClient(?ClientEntity $client): void
    {
        $this->client = $client;
    }

    /**
     * @return UserEntity|null
     */
    public function getUser(): ?UserEntity
    {
        return $this->user;
    }

    /**
     * @param UserEntity|null $user
     */
    public function setUser(?UserEntity $user): void
    {
        $this->user = $user;
    }



}
