<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\Contract;

use Cidaas\OpenAuth\Component\Contract\ClientInterface;
use Cidaas\OpenAuth\Exception\LoadClientException;
use Shopware\Core\Framework\Context;

interface ClientLoaderInterface
{
    /**
     * @throws LoadClientException
     */
    public function load(string $clientId, Context $context): ClientInterface;

    public function create(string $providerKey, Context $context): string;
}
