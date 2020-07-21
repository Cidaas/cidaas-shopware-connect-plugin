<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\Service;

use Cidaas\OpenAuth\Contract\UserKeyInterface;
use Cidaas\OpenAuth\Database\UserKeyCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\User\UserCollection;

class UserKey implements UserKeyInterface
{
    /**
     * @var EntityRepositoryInterface
     */
    private $userKeysRepository;

    public function __construct(EntityRepositoryInterface $userKeysRepository)
    {
        $this->userKeysRepository = $userKeysRepository;
    }

    public function add(string $userId, string $primaryKey, string $clientId, Context $context): string
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('primaryKey', $primaryKey),
            new EqualsFilter('userId', $userId),
            new EqualsFilter('clientId', $clientId)
        );
        $exists = $this->userKeysRepository->searchIds($criteria, $context);

        if ($exists->getTotal() > 0) {
            return $exists->firstId();
        }

        $id = Uuid::randomHex();
        $this->userKeysRepository->create([[
            'id' => $id,
            'userId' => $userId,
            'primaryKey' => $primaryKey,
            'clientId' => $clientId,
        ]], $context);

        return $id;
    }

    public function searchUser(string $primaryKey, string $clientId, Context $context): UserCollection
    {
        $criteria = new Criteria();
        $criteria->addAssociation('user');
        $criteria->addFilter(
            new EqualsFilter('primaryKey', $primaryKey),
            new EqualsFilter('clientId', $clientId)
        );
        /** @var UserKeyCollection $userKeys */
        $userKeys = $this->userKeysRepository->search($criteria, $context)->getEntities();
        $result = new UserCollection();

        foreach ($userKeys as $userKey) {
            $result->add($userKey->getUser());
        }

        return $result;
    }
}
