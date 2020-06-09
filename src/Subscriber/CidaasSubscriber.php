<?php declare(strict_types=1);

namespace Cidaas\Oauth2Connect\Subscriber;

use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\Content\Product\ProductEvents;

class CidaasSubscriber implements EventSubscriberInterface {

    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->systemConfigService = $systemConfigService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductEvents::PRODUCT_LOADED_EVENT => 'onProductsLoaded'
        ];
    }

    public function onProductsLoaded(EntityLoadedEvent $event): void
    {
        $clientId = $this->systemConfigService->get('Oauth2Connect.config.clientId');
        $clientSecret = $this->systemConfigService->get('Oauth2Connect.config.clientSecret');
        $redirectUri = $this->systemConfigService->get('Oauth2Connect.config.redirectUri');
        $grantType = $this->systemConfigService->get('Oauth2Connect.config.grantType');
        $authorizationUri = $this->systemConfigService->get('Oauth2Connect.config.authorizationUri');
        $tokenUri = $this->systemConfigService->get('Oauth2Connect.config.tokenUri');        
    }
}