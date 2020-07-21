<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\Service\Provider;

use Cidaas\OpenAuth\Component\Contract\ClientInterface;
use Cidaas\OpenAuth\Component\Provider\CidaasClient;
use Cidaas\OpenAuth\Contract\ProviderInterface;
use Cidaas\OpenAuth\Contract\TokenPairFactoryInterface;
use Cidaas\OpenAuth\Exception\ProvideClientInvalidConfigurationException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Throwable;

class CidaasProvider implements ProviderInterface
{
    /**
     * @var TokenPairFactoryInterface
     */
    private $tokenPairFactory;

    /**
     * @var EntityRepositoryInterface
     */
    private $clientsRepository;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var CidaasConfigurationResolverFactory
     */
    private $configurationResolverFactory;

    public function __construct(
        TokenPairFactoryInterface $tokenPairFactory,
        EntityRepositoryInterface $clientsRepository,
        RouterInterface $router,
        CidaasConfigurationResolverFactory $configurationResolverFactory
    ) {
        $this->tokenPairFactory = $tokenPairFactory;
        $this->clientsRepository = $clientsRepository;
        $this->router = $router;
        $this->configurationResolverFactory = $configurationResolverFactory;
    }

    public function provides(): string
    {
        return 'cidaas';
    }

    public function initializeClientConfiguration(string $clientId, Context $context): void
    {
        $this->clientsRepository->update([[
            'id' => $clientId,
            'config' => [
                'clientId' => '',
                'clientSecret' => '',
                'redirectUri' => $this->router->generate('administration.cidaas.open_auth.login', [
                    'clientId' => $clientId,
                ], UrlGeneratorInterface::ABSOLUTE_URL),
                'scopes' => [],
            ],
            'active' => false,
            'login' => true,
            'connect' => true,
            'store_user_token' => true,
            'provider' => 'cidaas',
        ]], $context);
    }

    public function provideClient(string $clientId, array $config, Context $context): ClientInterface
    {
        try {
            $values = $this->configurationResolverFactory->getOptionResolver($clientId, $context)->resolve($config);
        } catch (Throwable $e) {
            throw new ProvideClientInvalidConfigurationException($clientId, self::class, $e->getMessage(), $e);
        }

        return new CidaasClient($this->tokenPairFactory, $values);
    }
}
