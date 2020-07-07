<?php declare(strict_types=1);

namespace WidasCidaasExtension\Service;

use WidasCidaasExtension\Contract\UserEmailInterface;
use WidasCidaasExtension\Database\UserEmailCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\User\UserCollection;

class UserEmail implements UserEmailInterface
{
    /**
     * @var EntityRepositoryInterface
     */
    private $userEmailsRepository;

    public function __construct(EntityRepositoryInterface $userEmailsRepository)
    {
        $this->userEmailsRepository = $userEmailsRepository;
    }

    public function add(string $userId, string $email, string $clientId, Context $context): string
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('email', $email),
            new EqualsFilter('userId', $userId)
        );
        $exists = $this->userEmailsRepository->searchIds($criteria, $context);

        if ($exists->getTotal() > 0) {
            return $exists->firstId();
        }

        $id = Uuid::randomHex();
        $this->userEmailsRepository->create([[
            'id' => $id,
            'userId' => $userId,
            'email' => $email,
            'clientId' => $clientId,
        ]], $context);

        return $id;
    }

    public function searchUser(array $emails, Context $context): UserCollection
    {
        $criteria = new Criteria();
        $criteria->addAssociation('user');
        $criteria->addFilter(new EqualsAnyFilter('email', $emails));
        /** @var UserEmailCollection $userEmails */
        $userEmails = $this->userEmailsRepository->search($criteria, $context)->getEntities();
        $result = new UserCollection();

        foreach ($userEmails as $userEmail) {
            $result->add($userEmail->getUser());
        }

        return $result;
    }
}
