<?php declare(strict_types=1);

namespace Cidaas\OauthConnect\Database\Login;

use Cidaas\OauthConnect\Database\Client\ClientEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\User\UserEntity;

class LoginEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string|null
     */
    protected $clientId;

    /**
     * @var string|null
     */
    protected $state;

    /**
     * @var string|null
     */
    protected $userId;

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
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string|null $state
     */
    public function setState(?string $state): void
    {
        $this->state = $state;
    }

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


