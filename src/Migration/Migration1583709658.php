<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1583709658 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1583709658;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<'SQL'
UPDATE
    `cidaas_open_auth_client`
SET
    `config` = JSON_INSERT(
        JSON_REMOVE(config, '$.appId'),
        '$.clientId',
        JSON_EXTRACT(config, '$.appId')
    )
WHERE
    `provider` = 'cidaas';
SQL;
        $connection->executeQuery($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
