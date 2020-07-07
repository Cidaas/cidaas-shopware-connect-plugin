<?php declare(strict_types=1);

namespace WidasCidaasExtension\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1594104651 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1594104651;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<'SQL'
UPDATE
    `widas_cidaas_extension_client`
SET
    `store_user_token` = JSON_EXTRACT(config, '$.storeToken')
WHERE
    `provider` = 'cidaas';

UPDATE
    `widas_cidaas_extension_client`
SET
    `config` = JSON_REMOVE(config, '$.storeToken')
WHERE
    `provider` = 'cidaas';
SQL;
        $connection->executeQuery($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
