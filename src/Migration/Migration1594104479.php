<?php declare(strict_types=1);

namespace WidasCidaasExtension\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1594104479 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1594104479;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<'SQL'
UPDATE
    `widas_cidaas_extension_client`
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
