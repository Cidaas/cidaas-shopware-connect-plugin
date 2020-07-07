<?php declare(strict_types=1);

namespace WidasCidaasExtension\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1594104415 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1594104415;
    }

    public function update(Connection $connection): void
    {
    }

    public function updateDestructive(Connection $connection): void
    {
        $connection->executeQuery('ALTER TABLE `widas_cidaas_extension_login` DROP COLUMN `password`');
    }
}
