<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\Service\Provider;

use Cidaas\OpenAuth\Contract\ClientFeatureCheckerInterface;
use Cidaas\OpenAuth\Contract\ProviderConfigurationResolverFactoryInterface;
use Shopware\Core\Framework\Context;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CidaasConfigurationResolverFactory implements ProviderConfigurationResolverFactoryInterface
{
    /**
     * @var ClientFeatureCheckerInterface
     */
    private $clientFeatureChecker;

    public function __construct(ClientFeatureCheckerInterface $clientFeatureChecker)
    {
        $this->clientFeatureChecker = $clientFeatureChecker;
    }

    public function getOptionResolver(string $clientId, Context $context): OptionsResolver
    {
        $result = new OptionsResolver();
        $result->setDefined([
            'clientId',
            'clientSecret',
            'redirectUri',
            'scopes',
        ]);

        $result->setRequired([
            'clientId',
            'clientSecret',
            'redirectUri',
        ]);

        $result->setDefaults([
            'scopes' => [],
        ]);

        $result->setAllowedTypes('clientId', 'string');
        $result->setAllowedTypes('clientSecret', 'string');
        $result->setAllowedTypes('redirectUri', 'string');
        $result->setAllowedTypes('scopes', 'array');

        $result->addNormalizer('scopes', function (Options $options, $value) use ($context, $clientId) {
            $scopes = (array) $value;

            if ($this->clientFeatureChecker->canStoreUserTokens($clientId, $context)) {
                $scopes[] = 'offline_access';
            }

            return \array_unique(\array_merge($scopes, [
                'profile',
                'email',
                'openid',
                'roles'                
            ]));
        });

        return $result;
    }
}
