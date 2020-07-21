<?php declare(strict_types=1);

namespace Cidaas\OpenAuth;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class CidaasOpenAuth extends Plugin
{
    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);
        $uninstallContext->enableKeepMigrations();
    }
}