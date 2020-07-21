<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1582942221 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1582942221;
    }

    public function update(Connection $connection): void
    {
    }

    public function updateDestructive(Connection $connection): void
    {
        $connection->executeQuery('ALTER TABLE `cidaas_open_auth_login` DROP COLUMN `password`');
    }
}
