<?php declare(strict_types=1);

namespace Cidaas\OauthConnect\Database\UserKey;

use Cidaas\OauthConnect\Database\Client\ClientEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\User\UserEntity;

class UserKeyEntity extends Entity
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
    protected $primaryKey;

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
    public function getPrimaryKey(): ?string
    {
        return $this->primaryKey;
    }

    /**
     * @param string|null $primaryKey
     */
    public function setPrimaryKey(?string $primaryKey): void
    {
        $this->primaryKey = $primaryKey;
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
