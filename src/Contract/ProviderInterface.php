<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\Contract;

use Cidaas\OpenAuth\Component\Contract\ClientInterface;
use Cidaas\OpenAuth\Exception\ProvideClientException;
use Shopware\Core\Framework\Context;

interface ProviderInterface
{
    public function provides(): string;

    public function initializeClientConfiguration(string $clientId, Context $context): void;

    /**
     * @throws ProvideClientException
     */
    public function provideClient(string $clientId, array $config, Context $context): ClientInterface;
}
