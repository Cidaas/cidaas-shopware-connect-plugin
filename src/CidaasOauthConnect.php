<?php declare(strict_types=1);

namespace Cidaas\OauthConnect;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class CidaasOauthConnect extends Plugin
{
    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);
        $uninstallContext->enableKeepMigrations();
    }
}