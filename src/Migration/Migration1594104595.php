<?php declare(strict_types=1);

namespace WidasCidaasExtension\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1594104595 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1594104595;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<'SQL'
ALTER TABLE
    `widas_cidaas_extension_client`
    ADD COLUMN `store_user_token` BOOLEAN NOT NULL DEFAULT TRUE AFTER `connect`;
SQL;
        $connection->executeQuery($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
