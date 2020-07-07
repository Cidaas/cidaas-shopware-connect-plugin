<?php declare(strict_types=1);

namespace WidasCidaasExtension\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1594104388 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1594104388;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `widas_cidaas_extension_user_token` (
    `id` BINARY(16) NOT NULL,
    `user_id` BINARY(16) NOT NULL,
    `client_id` BINARY(16) NOT NULL,
    `refresh_token` MEDIUMTEXT NOT NULL,
    `access_token` MEDIUMTEXT NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),
    PRIMARY KEY (`id`),
    CONSTRAINT `fk.widas_cidaas_extension_user_token.client_id`
		FOREIGN KEY (`client_id`) REFERENCES `widas_cidaas_extension_client` (`id`)
			ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk.widas_cidaas_extension_user_token.user_id`
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
