<?php declare(strict_types=1);

namespace WidasCidaasExtension\Component\Contract;


use League\OAuth2\Client\Tool\RequestFactory;

interface AuthorizedHttpClientInterface
{
    public function getClient(): \GuzzleHttp\ClientInterface;

    public function getRequestFactory(): RequestFactory;

    public function getHeaders(): array;
}