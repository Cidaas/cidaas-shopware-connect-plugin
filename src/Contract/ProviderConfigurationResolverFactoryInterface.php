<?php declare(strict_types=1);

namespace WidasCidaasExtension\Contract;

use Shopware\Core\Framework\Context;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface ProviderConfigurationResolverFactoryInterface
{
    public function getOptionResolver(string $clientId, Context $context): OptionsResolver;
}