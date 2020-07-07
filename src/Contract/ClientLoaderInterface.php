<?php declare(strict_types=1);

namespace WidasCidaasExtension\Contract;

use Shopware\Core\Framework\Context;
use WidasCidaasExtension\Component\Contract\ClientInterface;
use WidasCidaasExtension\Exception\LoadClientException;

interface ClientLoaderInterface
{
    /**
     * @throws LoadClientException
     */
    public function load(string $clientId, Context $context): ClientInterface;

    public function create(string $providerKey, Context $context): string;
}