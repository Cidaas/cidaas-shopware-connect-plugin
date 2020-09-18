<?php declare(strict_types=1);

namespace Cidaas\OauthConnect\Controller;

use GuzzleHttp\Exception\ClientException;


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Client;
use Shopware\Core\Checkout\Customer\SalesChannel\AbstractLoginRoute;
use Shopware\Core\Checkout\Customer\SalesChannel\AbstractLogoutRoute;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Checkout\Customer\Exception\BadCredentialsException;
use Shopware\Core\Checkout\Customer\Exception\InactiveCustomerException;
use Shopware\Core\Checkout\Customer\SalesChannel\AbstractRegisterRoute;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @RouteScope(scopes={"storefront"})
 */

class CidaasController extends StorefrontController
{

    /**
     * @var AbstractLoginRoute
     */
    private $loginRoute;

    /**
     * @var AbstractLogoutRoute
     */
    private $logoutRoute;

    /**
     * @var SystemConfigService
     */
    private $systemConfig;

    /**
     * @var EntityrepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AbstractRegisterRoute
     */
    private $registerRoute;

     /**
     * @var Connection
     */
    protected $connection;

	private $state;


    public function __construct(
        AbstractLoginRoute $loginRoute,
        SystemConfigService $systemConfig,
        AbstractLogoutRoute $logoutRoute,
        EntityRepositoryInterface $customerRepository,
        AbstractRegisterRoute $registerRoute,
        Connection $connection
    ) {
        $this->loginRoute = $loginRoute;
        $this->logoutRoute = $logoutRoute;
        $this->systemConfig = $systemConfig;
        $this->customerRepository = $customerRepository;
        $this->registerRoute = $registerRoute;
        $this->connection = $connection;
        

    }

     /**
     * @Route ("/cidaas/login", name="cidaas.login", methods={"GET"})
     */

    public function Cidaaslogin()
    {
        $provider = $this->getProvider();
        $this->state = $this->generateRandomString();
        $authorizationUrl = $provider['urlAuthorize'].'?scope='.$provider['scopes'].'&response_type=code&approval_prompt=auto&redirect_uri=';
        $authorizationUrl .= urlencode($provider['redirectUri']).'&client_id='.$provider['clientId'];
        $authorizationUrl .= '&state='.$this->state;
        return new RedirectResponse($authorizationUrl, Response::HTTP_TEMPORARY_REDIRECT);
        // redirect to authorizationURL
    }

    /**
     * @Route ("/cidaas/register", name="cidaas.register", methods={"GET"})
     */
    public function register()
    {
    $provider = $this->getProvider();
    $this->state = $this->generateRandomString();
    $authorizationUrl = $provider['urlAuthorize'].'?scope='.$provider['scopes'].'&response_type=code&approval_prompt=auto&redirect_uri=';
    $authorizationUrl .= $provider['redirectUri'].'&client_id='.$provider['clientId'];
    $authorizationUrl .= '&view_type=register';
    $authorizationUrl .= '&state='.$this->state;
    return new RedirectResponse($authorizationUrl, Response::HTTP_TEMPORARY_REDIRECT);
    // redirect to authorizationURL
    }


     /**
     * @Route("/cidaas/redirect", name="cidaas.redirect", methods={"GET"})
     */
    public function redirectAfterResponse(Request $request, SalesChannelContext $context)
    {
        $provider = $this->getProvider();

        //Get authorization code
        $code = $request->query->get('code'); 
        if(empty($code))
        {
            $errorSnippet = "Login error : Error in receiving authorization code" ;
            $this->addFlash('danger',$errorSnippet);
            return $this->forwardToRoute(
                'frontend.home.page',
                [
                    'loginError' => true,
                    'errorSnippet' => $errorSnippet ?? null,
                ]
            );
        }
        $reqState = $request->query->get('state');
        if(!$reqState === $this->state){
            $errorSnippet = "Login error : State mismatch" ;
            $this->addFlash('danger',$errorSnippet);
            return $this->forwardToRoute(
                'frontend.home.page',
                [
                    'loginError' => true,
                    'errorSnippet' => $errorSnippet ?? null,
                ]
            );
        }

        //Get access token
        
        $accessToken = $this->getAccessToken($code,$provider,$request);
        
        if(isset($accessToken['error']))
        {
            $errorSnippet = $accessToken['error_description'] ;
            $this->addFlash('danger',$errorSnippet);
            return $this->forwardToRoute(
                'frontend.home.page',
                [
                    'loginError' => true,
                    'errorSnippet' => $errorSnippet ?? null,
                ]
            );
        }
        
        //Get resource owner details
        $resourceOwner = $this->getResourceOwner($accessToken,$provider,$request);
        if(isset($resourceOwner['error']))
        {
            $errorSnippet = 'Error retrieving resource owner :'.$resourceOwner['error'] ;
            $this->addFlash('danger',$errorSnippet);
            return $this->forwardToRoute(
                'frontend.home.page',
                [
                    'loginError' => true,
                    'errorSnippet' => $errorSnippet ?? null,
                ]
            );
        }
        $resourceOwner = $this->array_flatten($resourceOwner);

        //Check whether all required fields are present
        $requiredfields = ['salutation','billing_address_country','given_name','family_name','email','sw_cred','billing_address_street','billing_address_zipcode','billing_address_city'];
            $errors = [];
            foreach($requiredfields as $field)
            {
                if(!isset($resourceOwner[$field]) || empty($resourceOwner[$field]))
                {
                    array_push($errors,$field);
                }
            }
            if(!empty($errors))
            {
                $errorlist="";
                foreach($errors as $error)
                {
                    $errorlist.=$error." ";
                }
                $errormessage = "Error in registartion, ".count($errors)." fields are missing :".$errorlist;
                $this->addFlash('danger',$errormessage);
                return $this->forwardToRoute(
                    'frontend.home.page',
                    [
                        'loginError' => true,
                        'errorSnippet' => $errormessage ?? null,
                    ]
                );
            }
        
        //Search the db for user
        $this->customerRepository = $this->container->get('customer.repository');
        $entities = $this->customerRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('email', $resourceOwner['email'])),
            \Shopware\Core\Framework\Context::createDefaultContext()
        );
        $entity = $entities->getTotal();
        $password = json_decode($resourceOwner['sw_cred']);

        //If user not found then register
        if(empty($entity))
        {
            
            $queryBuilderCountry = $this->connection->createQueryBuilder();
            $queryBuilderCountry->select('country_id')
                ->from('country_translation')
                ->where('name = :name')
                ->setParameter('name', $resourceOwner['billing_address_country']);
            $countries = $queryBuilderCountry->execute()->fetchAll(FetchMode::COLUMN);
            $queryBuilderSalutation = $this->connection->createQueryBuilder();
            $queryBuilderSalutation->select('id')
                ->from('salutation')
                ->where('salutation_key = :salutation_key')
                ->setParameter('salutation_key', $resourceOwner['salutation']);

            $salutationId = $queryBuilderSalutation->execute()->fetchAll(FetchMode::COLUMN);

            $data = new RequestDataBag([
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
        try{
          $this->registerRoute->register($data, $context, true);
         }
         catch(ConstraintViolationException $formViolations)
         {
             $errorSnippet = $formViolations->getMessage();
             $this->addFlash('danger','Error in Registration: '.$errorSnippet);
                return $this->forwardToRoute(
                    'frontend.home.page',
                    [
                        'loginError' => true,
                        'errorSnippet' => $errorSnippet ?? null,
                    ]
                );
         }
        }
        
        //login the user
        $req = Request::create('/account/login','POST',['redirectTo'=>'frontend.account.home.page']);
        $data = new RequestDataBag(['username' => $resourceOwner['email'],'password'=> $password->{'sw_password'}]);

        try {
            $token = $this->loginRoute->login($data, $context)->getToken();
            if (!empty($token)) {
                return $this->createActionResponse($req);
            }
        } catch (BadCredentialsException | UnauthorizedHttpException | InactiveCustomerException $e) {
            if ($e instanceof InactiveCustomerException) {
                $errorSnippet = $e->getSnippetKey();
            }
        }

        $data->set('password', null);
        $this->addFlash('danger','Failed to login the user');
        return $this->forwardToRoute(
            'frontend.home.page',
            [
                'loginError' => true,
                'errorSnippet' => $errorSnippet ?? null,
            ]
        );

    }

    /**
     * @Route("/cidaas/logout", name="cidaas.logout", methods={"GET"})
     */
    public function logout(Request $request, SalesChannelContext $context)
    {


        if ($context->getCustomer() === null) {
            return $this->redirectToRoute('frontend.home.page');
        }

        try {
            $this->logoutRoute->logout($context);
            $salesChannelId = $context->getSalesChannel()->getId();
            if ($request->hasSession() && $this->systemConfig->get('core.loginRegistration.invalidateSessionOnLogOut', $salesChannelId)) {
                $request->getSession()->invalidate();
            }

            $this->addFlash('success', $this->trans('account.logoutSucceeded'));

            $parameters = [];
        } catch (ConstraintViolationException $formViolations) {
            $parameters = ['formViolations' => $formViolations];
        }

        return $this->redirectToRoute('frontend.home.page', $parameters);
    }


    protected function getResourceOwner($token, $provider, $request)
    {
     $userAgent = $request->headers->get('User-Agent').' cidaas-sw-plugin/1.0.1';
     $acceptlanguage = $request->headers->get('Accept');
     $client = new Client();
     //TODO:ERROR Handling we cannot expect to always receive a ressourceOwner, what happens with json_decode if not
     try{
     $response = $client->get($provider['urlResourceOwnerDetails'],[
         'headers' => [
            'content_type' => 'application/json',
            'accept_language' => $acceptlanguage,
             'access_token' => $token['access_token'],
             'user_agent' => $userAgent
         ]
     ]);
         }
         catch(ClientException $e) {
             $errormessage = json_decode($e->getResponse()->getBody()->getContents());
             $error = (array) $errormessage;
             return $error; 
         }
     $responseBody = json_decode($response->getBody()->getContents(),true);
     return $responseBody;
    }


    protected function getAccessToken($code, $provider,$request) {
    $userAgent = $request->headers->get('User-Agent').' cidaas-sw-plugin/1.0.1';
    $acceptlanguage = $request->headers->get('Accept');
    $client = new Client();
    try {
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
         }
         catch (ClientException $e) {
            $errormessage = json_decode($e->getResponse()->getBody()->getContents());
            $error = (array) $errormessage;
            return $error;
        }
        $responseBody = json_decode($response->getBody()->getContents(),true);
        return $responseBody;
    }

    protected function getProvider() {
        $redirectUri = getenv('APP_URL').'/cidaas/redirect';
        $provider = [
            'clientId' => $this->systemConfig->get('CidaasOauthConnect.config.clientId'),
            'clientSecret' => $this->systemConfig->get('CidaasOauthConnect.config.clientSecret'),
            'redirectUri' => $redirectUri,
            'urlAuthorize' => $this->systemConfig->get('CidaasOauthConnect.config.authorizationUri'),
            'urlAccessToken' => $this->systemConfig->get('CidaasOauthConnect.config.tokenUri'),
            'urlResourceOwnerDetails' => $this->systemConfig->get('CidaasOauthConnect.config.userUri'),
            'scopes' => "openid email profile",
        ];
        return $provider;
    }



    protected function array_flatten($array, $prefix = '') {

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

    protected function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }


}
