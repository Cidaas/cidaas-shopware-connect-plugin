<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\Service;

use Cidaas\OpenAuth\Contract\ClientFeatureCheckerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ClientFeatureChecker implements ClientFeatureCheckerInterface
{
    /**
     * @var EntityRepositoryInterface
     */
    private $clientsRepository;

    public function __construct(EntityRepositoryInterface $clientsRepository)
    {
        $this->clientsRepository = $clientsRepository;
    }

    public function canLogin(string $clientId, Context $context): bool
    {
        $criteria = new Criteria();
        $criteria->setIds([$clientId]);
        $criteria->addFilter(
            new EqualsFilter('active', true),
            new EqualsFilter('login', true)
        );

        return $this->clientsRepository->searchIds($criteria, $context)->firstId() !== null;
    }

    public function canConnect(string $clientId, Context $context): bool
    {
        $criteria = new Criteria();
        $criteria->setIds([$clientId]);
        $criteria->addFilter(
            new EqualsFilter('active', true),
            new EqualsFilter('connect', true)
        );

        return $this->clientsRepository->searchIds($criteria, $context)->firstId() !== null;
    }

    public function canStoreUserTokens(string $clientId, Context $context): bool
    {
        $criteria = new Criteria();
        $criteria->setIds([$clientId]);
        $criteria->addFilter(new EqualsFilter('storeUserToken', true));

        return $this->clientsRepository->searchIds($criteria, $context)->firstId() !== null;
    }
}
