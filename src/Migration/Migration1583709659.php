<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1583709659 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1583709659;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<'SQL'
UPDATE
    `cidaas_open_auth_client`
SET
    `config` = JSON_INSERT(
        JSON_REMOVE(config, '$.appSecret'),
        '$.clientSecret',
        JSON_EXTRACT(config, '$.appSecret')
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
