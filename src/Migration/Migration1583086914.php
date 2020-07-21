<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1583086914 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1583086914;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<'SQL'
ALTER TABLE
    `cidaas_open_auth_client`
    ADD COLUMN `active` BOOLEAN NOT NULL DEFAULT TRUE AFTER `provider`,
    ADD COLUMN `login` BOOLEAN NOT NULL DEFAULT TRUE AFTER `active`,
    ADD COLUMN `connect` BOOLEAN NOT NULL DEFAULT TRUE AFTER `login`;
SQL;
        $connection->executeQuery($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
