<?php declare(strict_types=1);

namespace Cidaas\OauthConnect\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\HttpFoundation\Request;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Demodata\DemodataContext;
use Shopware\Core\Framework\Demodata\Generator\CustomerGenerator;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @RouteScope(scopes={"storefront"})
 */

class FrontendController extends StorefrontController
{
    
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    /**
     * @var EntityrepositorynInterface
     */
    private $customerRepository;

    /**
     * @var GenericProvider
     */
    protected $provider;

    /**
     * @var Connection
     */
    protected $connection;

    protected $salutationIds;

    public function __construct(SystemConfigService $systemConfigService,EntityRepositoryInterface $customerRepository,Connection $connection)
    {
        $this->systemConfigService = $systemConfigService;
        $this->customerRepository = $customerRepository;
        $this->connection = $connection;
    }

    /**
     * @Route ("/cidaas/login", name="frontend.login", methods={"GET"})
     */
    public function login()
    {
        $provider = $this->getProvider();
        $authorizationUrl = $provider->getAuthorizationUrl();
        return new RedirectResponse($authorizationUrl, Response::HTTP_TEMPORARY_REDIRECT);
        // redirect to authorizationURL
    }

    /**
     * @Route("/cidaas/redirect", name="frontend.redirect", methods={"GET"})
     */
    public function redirectAfterResponse(Request $request)
    {
        $state = $request->query->get('state');
        $code = $request->query->get('code');
        $accessToken = $this->getAccessToken($code);
        $resourceOwner = $this->getResourceOwner($accessToken);
        $this->customerRepository = $this->container->get('customer.repository');
      /*  $entities = $this->customerRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('lastName', 'mallela')),
            \Shopware\Core\Framework\Context::createDefaultContext()
        );
        **/
        $countries = $this->connection
        ->executeQuery('SELECT id FROM country WHERE active = 1')
        ->fetchAll(FetchMode::COLUMN);
         
        $this->customerRepository->create(
            [
                [
                    'groupId' => Defaults::FALLBACK_CUSTOMER_GROUP,
                    'firstName' => $resourceOwner['name'],
                    'lastName' => 'test',
                    'email' => 'developer@widas.in',
                    'defaultPaymentMethodId' => $this->getDefaultPaymentMethod(),
                    'salesChannelId' => Defaults::SALES_CHANNEL,
                    'defaultBillingAddressId' => Uuid::randomHex(),
                    'defaultShippingAddressId' => Uuid::randomHex(),
                    'salutationId' => Uuid::fromBytesToHex($this->getRandomSalutationId()),
                    'customerNumber' => $resourceOwner['sub'],
                    'addresses' => [
                        [
                            'id' => Uuid::randomHex(),
                            'customerId' => Uuid::randomHex(),
                            'countryId' => Uuid::fromBytesToHex($countries[array_rand($countries)]),
                            'salutationId' => Uuid::fromBytesToHex($this->getRandomSalutationId()),
                            'firstName' => 'Max',
                            'lastName' => 'Mustermann',
                            'street' => 'Ebbinghoff 10',
                            'zipcode' => '48624',
                            'city' => 'Schöppingen',
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'customerId' => Uuid::randomHex(),
                            'countryId' => Uuid::fromBytesToHex($countries[array_rand($countries)]),
                            'salutationId' => Uuid::fromBytesToHex($this->getRandomSalutationId()),
                            'firstName' => 'Max',
                            'lastName' => 'Mustermann',
                            'street' => 'Bahnhofstraße 27',
                            'zipcode' => '10332',
                            'city' => 'Berlin',
                        ],
                ],
                    
                ]    
            ],
            \Shopware\Core\Framework\Context::createDefaultContext()
        );
        
        
        return new RedirectResponse('192.168.33.10', Response::HTTP_TEMPORARY_REDIRECT);
    }

    protected function getResourceOwner(AccessToken $token)
    {
       return  $this->getProvider()->getResourceOwner($token)->toArray();
        
    }


    protected function getAccessToken($code)
    { 
      $accessToken = $this->getProvider()->getAccessToken('authorization_code', [ 'code' => $code,]);
      return $accessToken;
    }

    protected function getProvider()
    {
        $redirectUri = 'http://192.168.33.10/cidaas/redirect';
        $this->provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId' => $this->systemConfigService->get('CidaasOauthConnect.config.clientId'),
            'clientSecret' => $this->systemConfigService->get('CidaasOauthConnect.config.clientSecret'),
            'redirectUri' => $redirectUri,
            'urlAuthorize' => $this->systemConfigService->get('CidaasOauthConnect.config.authorizationUri'),
            'urlAccessToken' => $this->systemConfigService->get('CidaasOauthConnect.config.tokenUri'),
            'urlResourceOwnerDetails' => $this->systemConfigService->get('CidaasOauthConnect.config.userUri'),
            'scopes' => "openid email profile",
        ]);
        return $this->provider;

    }

    private function getDefaultPaymentMethod(): ?string
    {
        $id = $this->connection->executeQuery(
            'SELECT `id` FROM `payment_method` WHERE `active` = 1 ORDER BY `position` ASC'
        )->fetchColumn();

        if (!$id) {
            return null;
        }

        return Uuid::fromBytesToHex($id);
    }

    private function getRandomSalutationId(): string
    {
        if (!$this->salutationIds) {
            $this->salutationIds = $this->connection->executeQuery('SELECT id FROM salutation')->fetchAll(FetchMode::COLUMN);
        }

        return $this->salutationIds[array_rand($this->salutationIds)];
    }

}