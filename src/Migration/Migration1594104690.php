<?php declare(strict_types=1);

namespace WidasCidaasExtension\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1594104690 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1594104690;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<'SQL'
ALTER TABLE
    `widas_cidaas_extension_user_token`
ADD COLUMN `expires_at` DATETIME(3) NULL AFTER `refresh_token`;
SQL;
        $connection->executeQuery($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
