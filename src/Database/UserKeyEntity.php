<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\Database;

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

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function setClientId(string $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    public function setPrimaryKey(string $primaryKey): self
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }

    public function getClient(): ?ClientEntity
    {
        return $this->client;
    }

    public function setClient(?ClientEntity $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getUser(): ?UserEntity
    {
        return $this->user;
    }

    public function setUser(?UserEntity $user): self
    {
        $this->user = $user;

        return $this;
    }
}
