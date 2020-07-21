<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\Service;

use Cidaas\OpenAuth\Component\Contract\ClientInterface;
use Cidaas\OpenAuth\Contract\ClientLoaderInterface;
use Cidaas\OpenAuth\Contract\ProviderRepositoryInterface;
use Cidaas\OpenAuth\Database\ClientCollection;
use Cidaas\OpenAuth\Exception\LoadClientClientNotFoundException;
use Cidaas\OpenAuth\Exception\LoadClientException;
use Cidaas\OpenAuth\Exception\LoadClientMatchingProviderNotFoundException;
use Cidaas\OpenAuth\Exception\ProvideClientException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;

class ClientLoader implements ClientLoaderInterface
{
    /**
     * @var ProviderRepositoryInterface
     */
    private $providers;

    /**
     * @var EntityRepositoryInterface
     */
    private $clientsRepository;

    public function __construct(ProviderRepositoryInterface $providers, EntityRepositoryInterface $clientsRepository)
    {
        $this->providers = $providers;
        $this->clientsRepository = $clientsRepository;
    }

    public function load(string $clientId, Context $context): ClientInterface
    {
        $criteria = new Criteria();
        $criteria->setIds([$clientId]);

        /** @var ClientCollection $searchResult */
        $searchResult = $this->clientsRepository->search($criteria, $context)->getEntities();

        if ($searchResult->count() === 0) {
            throw new LoadClientClientNotFoundException($clientId);
        }

        foreach ($this->providers->getMatchingProviders($searchResult->first()->getProvider()) as $provider) {
            try {
                return $provider->provideClient($clientId, $searchResult->first()->getConfig() ?? [], $context);
            } catch (ProvideClientException $e) {
                throw new LoadClientException($e->getMessage(), $clientId, $e);
            }
        }

        throw new LoadClientMatchingProviderNotFoundException($clientId);
    }

    public function create(string $providerKey, Context $context): string
    {
        $id = Uuid::randomHex();

        $this->clientsRepository->create([[
            'id' => $id,
            'name' => $providerKey,
            'provider' => $providerKey,
            'active' => false,
            'login' => false,
            'connect' => false,
            'storeUserToken' => false,
            'config' => [],
        ]], $context);

        foreach ($this->providers->getMatchingProviders($providerKey) as $provider) {
            $provider->initializeClientConfiguration($id, $context);
        }

        return $id;
    }
}
