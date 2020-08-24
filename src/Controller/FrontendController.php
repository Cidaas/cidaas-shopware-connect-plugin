<?php declare(strict_types=1);





namespace Cidaas\OauthConnect\Controller;

use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;

use Shopware\Storefront\Page\Account\Login\AccountLoginPageLoader;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as HttpRequest;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\HttpFoundation\Request ;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Customer\SalesChannel\AbstractLogoutRoute;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function GuzzleHttp\Psr7\copy_to_string;

/**
 * @RouteScope(scopes={"storefront"})
 */

class FrontendController extends StoreFrontController
{
    
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    /**
     * @var EntityrepositoryInterface
     */
    private $customerRepository;


    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var CustomerEntity
     */
    protected $entities;

    /**
     * @var AbstractLogoutRoute
     */
    protected $logoutRoute;

    /**
     * @var AccountLoginPageLoader
     */
    protected $loginPageLoader;

  
    

    public function __construct(SystemConfigService $systemConfigService,EntityRepositoryInterface $customerRepository,Connection $connection,AbstractLogoutRoute $logoutRoute)
    {
        $this->systemConfigService = $systemConfigService;
        $this->customerRepository = $customerRepository;
        $this->connection = $connection;
        $this->logoutRoute = $logoutRoute;
    }

    /**
     * @Route ("/cidaas/login", name="frontend.login", methods={"GET"})
     */
    public function Cidaaslogin()
    {
        $provider = $this->getProvider();
        $authorizationUrl = $provider['urlAuthorize'].'?scope='.$provider['scopes'].'&response_type=code&approval_prompt=auto&redirect_uri=';
        $authorizationUrl .= urlencode($provider['redirectUri']).'&client_id='.$provider['clientId'];
        return new RedirectResponse($authorizationUrl, Response::HTTP_TEMPORARY_REDIRECT);
        // redirect to authorizationURL
    }

    /**
     * @Route ("/cidaas/register", name="frontend.register", methods={"GET"})
     */
    public function register()
    {
    $provider = $this->getProvider();
    $authorizationUrl = $provider['urlAuthorize'].'?scope='.$provider['scopes'].'&response_type=code&approval_prompt=auto&redirect_uri=';
    $authorizationUrl .= $provider['redirectUri'].'&client_id='.$provider['clientId'];
    $authorizationUrl = $authorizationUrl.'&view_type=register';
    return new RedirectResponse($authorizationUrl, Response::HTTP_TEMPORARY_REDIRECT);
    // redirect to authorizationURL
    }

    /**
     * @Route("/cidaas/redirect", name="frontend.redirect", methods={"GET"},defaults={"csrf_protected"=false})
     */
    public function redirectAfterResponse(Request $request, SalesChannelContext $context)
    {
        $provider = $this->getProvider();
        $code = $request->query->get('code');
        $accessToken = $this->getAccessToken($code,$provider);
        $resourceOwner = $this->getResourceOwner($accessToken,$provider);

        $resourceOwner = $this->array_flatten($resourceOwner);
        $this->customerRepository = $this->container->get('customer.repository');
        
        $entities = $this->customerRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('email', $resourceOwner['email'])),
            \Shopware\Core\Framework\Context::createDefaultContext()
        );
        $entity = $entities->getTotal();
        $password = json_decode($resourceOwner['sw_cred']);
        if(empty($entity))
        {
            $countries = $this->connection
            ->executeQuery("SELECT country_id FROM country_translation WHERE name = '". $resourceOwner["billing_address_country"]. "' ")
            ->fetchAll(FetchMode::COLUMN);
            
            $salutationId = $this->connection->executeQuery("SELECT id FROM salutation WHERE  salutation_key  = '". $resourceOwner["salutation"]. "' ")->fetchAll(FetchMode::COLUMN);
            //echo $password;
            $body = \json_encode([ 
                "guest" => false,
                "salutationId" => Uuid::fromBytesToHex($salutationId[0]),
                "firstName" => $resourceOwner['given_name'],
                "lastName" => $resourceOwner['family_name'],
                "email" => $resourceOwner['email'],
                'defaultBillingAddressId' => Uuid::randomHex(),
                'defaultShippingAddressId' => Uuid::randomHex(),
                "password" =>  $password->{'sw_password'},
                "billingAddress" => array(
                    "countryId" => Uuid::fromBytesToHex($countries[0]),
                    "street" => $resourceOwner['billing_address_street'],
                    "zipcode" => $resourceOwner['billing_address_zipcode'],
                    "city" => $resourceOwner['billing_address_city']
                ),
                "storefrontUrl" => getenv('APP_URL'),   
             ]);

             $accesskey = $this->connection->executeQuery('SELECT access_key from sales_channel inner join sales_channel_translation on id = sales_channel_id where name = "Storefront"')->fetchAll(FetchMode::COLUMN);
   
            $request = new HttpRequest(
                'POST',
                getenv('APP_URL'). '/store-api/v3/account/register',
                ['Content-Type' => 'application/json','sw-access-key' => $accesskey],
                $body
            );
    
            $client = new Client();
            try {
            $response = $client->send($request);
            }
         catch (\GuzzleHttp\Exception\ClientException $e) {
    
            echo ($e->getResponse()->getBody()->getContents());
    
        }

        }

        $client = new Client();
        $response = $client->post(getenv('APP_URL').'/csrf/generate');
        $cookie = $response->getHeader('Set-Cookie');
        $responseBody = json_decode($response->getBody()->getContents(),true);
        $csrf = $responseBody['token'];
        
        //echo $cookie[0];
        $client = new Client(['redirect.disable' => true]);
        $response = $client->post(getenv('APP_URL').'/account/login', [
        'form_params' => [
        'username' => $resourceOwner['email'],
        'password' => $password->{'sw_password'},
        'redirectTo' => 'frontend.account.home.page',
        '_csrf_token' => $csrf
        ],
        'headers' => ['Cookie' => $cookie],
        'allow_redirects' => false
        ]);
        $res = new Response();
        foreach ($response->getHeaders() as $name => $values) {
        if($name != 'Transfer-Encoding') {
        $res->headers->set($name, $values);
        }
        }
        $res->setContent(copy_to_string($response->getBody()));
        return $res;

    }

    /**
     * @Route("/cidaas/logout", name="frontend.logout", methods={"GET"})
     */
    public function logout(Request $request, SalesChannelContext $context)
    {
        

        if ($context->getCustomer() === null) {
            return $this->redirectToRoute('frontend.home.page');
        }

        try {
            $this->logoutRoute->logout($context);
            $salesChannelId = $context->getSalesChannel()->getId();
            if ($request->hasSession() && $this->systemConfigService->get('core.loginRegistration.invalidateSessionOnLogOut', $salesChannelId)) {
                $request->getSession()->invalidate();
            }

            $this->addFlash('success', $this->trans('account.logoutSucceeded'));

            $parameters = [];
        } catch (ConstraintViolationException $formViolations) {
            $parameters = ['formViolations' => $formViolations];
        }

        return $this->redirectToRoute('frontend.home.page', $parameters);
    }


    protected function getResourceOwner($token,$provider)
    {
     $userAgent = $_SERVER['HTTP_USER_AGENT'].' cidaas-sw-plugin/1.0.1';
     $acceptlanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
     $host = $_SERVER['HTTP_HOST'];
     $client = new Client();
     $response = $client->get($provider['urlResourceOwnerDetails'],[
         'headers' => [
            'content_type' => 'application/json',
            'accept_language' => $acceptlanguage,
             'access_token' => $token['access_token'],
             'user_agent' => $userAgent,
             
         ]
     ]);
     $responseBody = json_decode($response->getBody()->getContents(),true);
     return $responseBody;
    }


    protected function getAccessToken($code, $provider)
    { 
     $userAgent = $_SERVER['HTTP_USER_AGENT'].' cidaas-sw-plugin/1.0.1';
     $acceptlanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
     $host = $_SERVER['HTTP_HOST'];
     $client = new Client();
     $response = $client->post($provider['urlAccessToken'],[
         'form_params' => [
             'grant_type' => 'authorization_code',
             'client_id' => $provider['clientId'],
             'client_secret' => $provider['clientSecret'],
             'code' => $code,
             'redirect_uri' => $provider['redirectUri']
         ],
         'headers' => [
             'content_type' => 'application/json',
             'accept_language' => $acceptlanguage,
             'user_agent' => $userAgent,
             
         ]
     ]);
     $responseBody = json_decode($response->getBody()->getContents(),true);
     return $responseBody;
    }

    protected function getProvider()
    {
        $redirectUri = getenv('APP_URL').'/cidaas/redirect';
        $this->provider = [
            'clientId' => $this->systemConfigService->get('CidaasOauthConnect.config.clientId'),
            'clientSecret' => $this->systemConfigService->get('CidaasOauthConnect.config.clientSecret'),
            'redirectUri' => $redirectUri,
            'urlAuthorize' => $this->systemConfigService->get('CidaasOauthConnect.config.authorizationUri'),
            'urlAccessToken' => $this->systemConfigService->get('CidaasOauthConnect.config.tokenUri'),
            'urlResourceOwnerDetails' => $this->systemConfigService->get('CidaasOauthConnect.config.userUri'),
            'scopes' => "openid email profile",
        ];
        return $this->provider;

    }


    protected function array_flatten($array, $prefix = '')
    {
    
       $result = array();   
         
    foreach($array as $key=>$value) {
         
            if(is_array($value) && $key !== "roles") {
                
                $result = $result + $this->array_flatten($value);
                
            }
            else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
    

}