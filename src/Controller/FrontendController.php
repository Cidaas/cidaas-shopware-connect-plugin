<?php declare(strict_types=1);

namespace Cidaas\OauthConnect\Controller;


use Shopware\Storefront\Controller\AuthController;



use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as HttpRequest;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\HttpFoundation\Request ;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    /**
     * @var CustomerEntity
     */
    protected $entities;

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
    public function Cidaaslogin()
    {
        $provider = $this->getProvider();
        $authorizationUrl = $provider->getAuthorizationUrl();
        return new RedirectResponse($authorizationUrl, Response::HTTP_TEMPORARY_REDIRECT);
        // redirect to authorizationURL
    }

    /**
     * @Route ("/cidaas/register", name="frontend.register", methods={"GET"})
     */
    public function register()
    {
    $provider = $this->getProvider();
    $authorizationUrl = $provider->getAuthorizationUrl();
    $authorizationUrl = $authorizationUrl.'&view_type=register';
    return new RedirectResponse($authorizationUrl, Response::HTTP_TEMPORARY_REDIRECT);
    // redirect to authorizationURL
    }

    /**
     * @Route("/cidaas/redirect", name="frontend.redirect", methods={"GET"})
     */
    public function redirectAfterResponse(Request $request, SalesChannelContext $context)
    {
        $state = $request->query->get('state');
        $code = $request->query->get('code');
        $accessToken = $this->getAccessToken($code);
        $resourceOwner = $this->getResourceOwner($accessToken);
        $this->customerRepository = $this->container->get('customer.repository');
        
        $entities = $this->customerRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('email', $resourceOwner['email'])),
            \Shopware\Core\Framework\Context::createDefaultContext()
        );
        $basecontext = \Shopware\Core\Framework\Context::createDefaultContext();

        //$pass = $this->connection->executeQuery('SELECT `password` FROM `customer` WHERE `email` = `gopi.mallela@widas.in` ' )->fetchAll(FetchMode::COLUMN);
        //echo implode(" ",$pass);
        if(!empty($entities))
        {
            $password = $entities->getEntities();
            //echo $password;
        }


        $countries = $this->connection
        ->executeQuery('SELECT id FROM country WHERE active = 1')
        ->fetchAll(FetchMode::COLUMN);
       /* 
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
        **/

        $client = new Client();
        $body = \json_encode([
            'grant_type' => 'password',
            'username' => 'gopi.mallela@widas.in',
            'password' => 'gopi'
        ]);
        $request = new HttpRequest(
            'POST',
            getenv('APP_URL') . '/api/oauth/token',
            ['Content-Type' => 'application/json'],
            $body
        );

        $response = $client->sendRequest($request);
        return $response;
        /*
        $body = \json_encode([
            'username' => 'gopi.mallela@widas.in',
            'password' => 'gopi'
        ]);
        
        $request = new HttpRequest(
            'POST',
            'http://192.168.33.10/account/login',
            ['Content-Type' => 'application/x-www-form-urlencoded','Authorization' => 'Bearer ' . 'SWSCMHQYNU51VJFJMM1GELFLZQ'],
            $body
        );
        $response = $client->send($request);
        return $response;
        //$body = json_decode($response->getBody()->getContents(), true);
        //$code = $body['contextToken'];
        //echo $code;
       /*
        $finalRoute = $this->generateUrl(
            'frontend.account.login',
            ['sw-context-token' => $code],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        return new RedirectResponse($finalRoute, Response::HTTP_TEMPORARY_REDIRECT);
        */
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