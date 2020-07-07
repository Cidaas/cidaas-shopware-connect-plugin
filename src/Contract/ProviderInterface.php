<?php declare(strict_types=1);

namespace WidasCidaasExtension\Contract;

use Shopware\Core\Framework\Context;
use WidasCidaasExtension\Component\Contract\ClientInterface;
use WidasCidaasExtension\Exception\ProvideClientException;

interface ProviderInterface
{
    public function provides(): string;

    public function initializeClientConfiguration(string $clientId, Context $context): void;

    /**
     * @throws ProvideClientException
     */
    public function provideClient(string $clientId, array $config, Context $context): ClientInterface;
}