<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\Exception;

use Throwable;

class LoadClientClientNotFoundException extends LoadClientException
{
    public function __construct(string $clientId, ?Throwable $previous = null)
    {
        parent::__construct('No client found to load by id ' . $clientId, $clientId, $previous);
    }
}
