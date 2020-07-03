<?php declare(strict_types=1);

namespace WidasCidaasExtension;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class WidasCidaasExtension extends Plugin
{
    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);
        $uninstallContext->enableKeepMigrations();
    }
}