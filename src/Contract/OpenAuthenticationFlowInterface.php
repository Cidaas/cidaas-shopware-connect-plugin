<?php declare(strict_types=1);

namespace WidasCidaasExtension\Contract;

use Shopware\Core\Framework\Context;
use WidasCidaasExtension\Exception\LoadClientException;

interface OpenAuthenticationFlowInterface
{
    /**
     * @throws LoadClientException
     */
    public function getRedirectUrl(string $clientId, Context $context): string;

    /**
     * @throws LoadClientException
     */
    public function getRedirectUrlToConnect(string $clientId, string $userId, Context $context): string;

    /**
     * @throws LoadClientException
     */
    public function upsertUser(string $clientId, string $state, string $code, Context $context): void;

    public function getLoginRoutes(Context $context): array;
}