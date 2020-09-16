<?php declare(strict_types=1);

namespace Cidaas\OauthConnect\Controller;

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
        $authorizationUrl = $provider['urlAuthorize'].'?scope='.$provider['scopes'].'&response_type=code&approval_prompt=auto&redirect_uri=';
        $authorizationUrl .= urlencode($provider['redirectUri']).'&client_id='.$provider['clientId'];
        $authorizationUrl .= '&state='.$provider['state'];
        return new RedirectResponse($authorizationUrl, Response::HTTP_TEMPORARY_REDIRECT);
        // redirect to authorizationURL
    }

    /**
     * @Route ("/cidaas/register", name="cidaas.register", methods={"GET"})
     */
    public function register()
    {
    $provider = $this->getProvider();
    $authorizationUrl = $provider['urlAuthorize'].'?scope='.$provider['scopes'].'&response_type=code&approval_prompt=auto&redirect_uri=';
    $authorizationUrl .= $provider['redirectUri'].'&client_id='.$provider['clientId'];
    $authorizationUrl .= '&view_type=register';
    $authorizationUrl .= '&state='.$provider['state'];
    return new RedirectResponse($authorizationUrl, Response::HTTP_TEMPORARY_REDIRECT);
    // redirect to authorizationURL
    }


     /**
     * @Route("/cidaas/redirect", name="cidaas.redirect", methods={"GET"})
     */
    public function redirectAfterResponse(Request $request, SalesChannelContext $context)
    {
        $provider = $this->getProvider();

        //TODO:Error-HANDLING if no code - then we cannot continue
        //Get authorization code
        $code = $request->query->get('code');


        //TODO:Error-HANDLING if accessToken not available we cannot continue, this means the user was not created in cidaas right?
        //Get access token
        $accessToken = $this->getAccessToken($code,$provider,$request);

        //TODO:Error-HANDLING if ressourceOwner not available we cannot continue, this might lead to duplicated entries in db
        //Get resource owner details
        $resourceOwner = $this->getResourceOwner($accessToken,$provider,$request);
        $resourceOwner = $this->array_flatten($resourceOwner);

        //TODO:Error-HANDLING if customerRepository not available - maybe database not available, then we also cannot continue, might lead to conflicting data
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

            $salutationId = $queryBuilderCountry->execute()->fetchAll(FetchMode::COLUMN);

            //TODO:Error-HANDLING if values not available
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

          $cust = $this->registerRoute->register($data, $context, true );
        }
        //TODO:Error-HANDLING if req fails, we are not creating the user in the database right?
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
                //TODO: we should log it?
                $errorSnippet = $e->getSnippetKey();
            }
        }

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
     $host = $request->headers->get('Host');
     $client = new Client();
     //TODO:ERROR Handling we cannot expect to always receive a ressourceOwner, what happens with json_decode if not
     $response = $client->get($provider['urlResourceOwnerDetails'],[
         'headers' => [
            'content_type' => 'application/json',
            'accept_language' => $acceptlanguage,
             'access_token' => $token['access_token'],
             'user_agent' => $userAgent,
             'host' => $host,
         ]
     ]);
     $responseBody = json_decode($response->getBody()->getContents(),true);
     return $responseBody;
    }


    protected function getAccessToken($code, $provider,$request) {
    $userAgent = $request->headers->get('User-Agent').' cidaas-sw-plugin/1.0.1';
    $acceptlanguage = $request->headers->get('Accept');
    $host = $request->headers->get('Host');
    $client = new Client();
    //TODO:ERROR Handling we cannot expect to always receive a accesstoken, what happens with json_decode if not
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
             'host' => $host,
         ]
     ]);
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
            'state' => generateRandomString(),
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

    protected function generateRandomString($length = 11) {
      return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }


}
