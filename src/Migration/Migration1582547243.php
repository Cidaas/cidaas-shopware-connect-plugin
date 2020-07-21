<?php declare(strict_types=1);

namespace Cidaas\OpenAuth\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1582547243 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1582547243;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `cidaas_open_auth_user_token` (
    `id` BINARY(16) NOT NULL,
    `user_id` BINARY(16) NOT NULL,
    `client_id` BINARY(16) NOT NULL,
    `refresh_token` MEDIUMTEXT NOT NULL,
    `access_token` MEDIUMTEXT NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),
    PRIMARY KEY (`id`),
    CONSTRAINT `fk.cidaas_open_auth_user_token.client_id`
		FOREIGN KEY (`client_id`) REFERENCES `cidaas_open_auth_client` (`id`)
			ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk.cidaas_open_auth_user_token.user_id`
		FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;
SQL;
        $connection->executeQuery($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
