<?php

declare(strict_types=1);

namespace Laminas\ApiTools\OAuth2\Controller;

use InvalidArgumentException;
use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use Laminas\ApiTools\ApiProblem\Exception\ProblemExceptionInterface;
use Laminas\ApiTools\ContentNegotiation\ViewModel;
use Laminas\ApiTools\OAuth2\Provider\UserId\UserIdProviderInterface;
use Laminas\Http\PhpEnvironment\Request as PhpEnvironmentRequest;
use Laminas\Http\Request as HttpRequest;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Stdlib\ResponseInterface;
use OAuth2\Request as OAuth2Request;
use OAuth2\Response as OAuth2Response;
use OAuth2\Server as OAuth2Server;
use RuntimeException;
use Webmozart\Assert\Assert;

use function gettype;
use function is_callable;
use function is_object;
use function json_encode;
use function sprintf;

class AuthController extends AbstractActionController
{
    /** @var boolean */
    protected $apiProblemErrorResponse = true;

    /** @var OAuth2Server */
    protected $server;

    /** @var callable Factory for generating an OAuth2Server instance. */
    protected $serverFactory;

    /** @var UserIdProviderInterface */
    protected $userIdProvider;

    /**
     * Constructor
     *
     * @param callable $serverFactory
     */
    public function __construct($serverFactory, UserIdProviderInterface $userIdProvider)
    {
        if (! is_callable($serverFactory)) {
            throw new InvalidArgumentException(sprintf(
                'OAuth2 Server factory must be a PHP callable; received %s',
                is_object($serverFactory) ? $serverFactory::class : gettype($serverFactory)
            ));
        }
        $this->serverFactory  = $serverFactory;
        $this->userIdProvider = $userIdProvider;
    }

    /**
     * Should the controller return ApiProblemResponse?
     *
     * @return bool
     */
    public function isApiProblemErrorResponse()
    {
        return $this->apiProblemErrorResponse;
    }

    /**
     * Indicate whether ApiProblemResponse or oauth2 errors should be returned.
     *
     * Boolean true indicates ApiProblemResponse should be returned (the
     * default), while false indicates oauth2 errors (per the oauth2 spec)
     * should be returned.
     *
     * @param bool $apiProblemErrorResponse
     * @return void
     */
    public function setApiProblemErrorResponse($apiProblemErrorResponse)
    {
        $this->apiProblemErrorResponse = (bool) $apiProblemErrorResponse;
    }

    /**
     * Token Action (/oauth)
     */
    public function tokenAction(): ?Response
    {
        $request = $this->getRequest();
        if (! $request instanceof HttpRequest) {
            // not an HTTP request; nothing left to do
            return null;
        }

        if ($request->isOptions()) {
            // OPTIONS request.
            // This is most likely a CORS attempt; as such, pass the response on.
            return $this->getResponse();
        }

        $oauth2request = $this->getOAuth2Request();
        $oauth2server  = $this->getOAuth2Server($this->params('oauth'));
        try {
            $response = $oauth2server->handleTokenRequest($oauth2request);
        } catch (ProblemExceptionInterface $ex) {
            $status = $ex->getCode() ?: 401;
            $status = $status >= 400 && $status < 600 ? $status : 401;

            return new ApiProblemResponse(
                new ApiProblem($status, $ex)
            );
        }

        if ($response->isClientError()) {
            return $this->getErrorResponse($response);
        }

        return $this->setHttpResponse($response);
    }

    /**
     * Token Revoke (/oauth/revoke)
     */
    public function revokeAction(): ?Response
    {
        $request = $this->getRequest();
        if (! $request instanceof HttpRequest) {
            // not an HTTP request; nothing left to do
            return null;
        }

        if ($request->isOptions()) {
            // OPTIONS request.
            // This is most likely a CORS attempt; as such, pass the response on.
            return $this->getResponse();
        }

        $oauth2request = $this->getOAuth2Request();
        $response      = $this->getOAuth2Server($this->params('oauth'))->handleRevokeRequest($oauth2request);

        if ($response->isClientError()) {
            return $this->getErrorResponse($response);
        }

        return $this->setHttpResponse($response);
    }

    /**
     * Test resource (/oauth/resource)
     */
    public function resourceAction(): Response
    {
        $server = $this->getOAuth2Server($this->params('oauth'));

        // Handle a request for an OAuth2.0 Access Token and send the response to the client
        if (! $server->verifyResourceRequest($this->getOAuth2Request())) {
            $response = $server->getResponse();
            Assert::isInstanceOf(
                $response,
                OAuth2Response::class,
                'Did not receive valid OAuth2 response instance from OAuth2 Server'
            );
            return $this->getApiProblemResponse($response);
        }

        $httpResponse = $this->getResponse();
        $httpResponse->setStatusCode(200);
        $httpResponse->getHeaders()->addHeaders(['Content-type' => 'application/json']);
        $httpResponse->setContent(
            json_encode(['success' => true, 'message' => 'You accessed my APIs!'])
        );
        return $httpResponse;
    }

    /**
     * Authorize action (/oauth/authorize)
     *
     * @return Response|ViewModel
     */
    public function authorizeAction()
    {
        $serverType = $this->params('oauth');
        Assert::nullOrStringNotEmpty($serverType);

        $this->getOAuth2Server($serverType);
        $request  = $this->getOAuth2Request();
        $response = new OAuth2Response();

        // validate the authorize request
        $isValid = $this->server->validateAuthorizeRequest($request, $response);

        if (! $isValid) {
            return $this->getErrorResponse($response);
        }

        $authorized = $request->request('authorized', false);
        if (empty($authorized)) {
            $clientId = $request->query('client_id', false);
            $view     = new ViewModel(['clientId' => $clientId]);
            $view->setTemplate('oauth/authorize');
            return $view;
        }

        $isAuthorized   = $authorized === 'yes';
        $userIdProvider = $this->userIdProvider;

        $this->server->handleAuthorizeRequest(
            $request,
            $response,
            $isAuthorized,
            $userIdProvider($this->getRequest())
        );

        $redirect = $response->getHttpHeader('Location');
        if (! empty($redirect)) {
            return $this->redirect()->toUrl($redirect);
        }

        return $this->getErrorResponse($response);
    }

    /**
     * Receive code action prints the code/token access
     */
    public function receiveCodeAction(): ViewModel
    {
        $code = $this->params()->fromQuery('code', false);
        $view = new ViewModel([
            'code' => $code,
        ]);
        $view->setTemplate('oauth/receive-code');
        return $view;
    }

    /**
     * @return ApiProblemResponse|ResponseInterface
     */
    protected function getErrorResponse(OAuth2Response $response)
    {
        if ($this->isApiProblemErrorResponse()) {
            return $this->getApiProblemResponse($response);
        }

        return $this->setHttpResponse($response);
    }

    /**
     * Map OAuth2Response to ApiProblemResponse
     *
     * @return ApiProblemResponse
     */
    protected function getApiProblemResponse(OAuth2Response $response)
    {
        $parameters       = $response->getParameters();
        $errorUri         = $parameters['error_uri'] ?? null;
        $error            = $parameters['error'] ?? null;
        $errorDescription = $parameters['error_description'] ?? null;

        return new ApiProblemResponse(
            new ApiProblem(
                $response->getStatusCode(),
                $errorDescription,
                $errorUri,
                $error
            )
        );
    }

    /**
     * Create an OAuth2 request based on the Laminas request object
     *
     * Marshals:
     *
     * - query string
     * - body parameters, via content negotiation
     * - "server", specifically the request method and content type
     * - raw content
     * - headers
     *
     * This ensures that JSON requests providing credentials for OAuth2
     * verification/validation can be processed.
     *
     * @return OAuth2Request
     */
    protected function getOAuth2Request()
    {
        $laminasRequest = $this->getRequest();
        $headers        = $laminasRequest->getHeaders();

        // Marshal content type, so we can seed it into the $_SERVER array
        if ($headers->has('Content-Type')) {
            $headers->get('Content-Type')->getFieldValue();
        }

        // Get $_SERVER superglobal
        $server = [];
        if ($laminasRequest instanceof PhpEnvironmentRequest) {
            $server = $laminasRequest->getServer()->toArray();
        } elseif (! empty($_SERVER)) {
            $server = $_SERVER;
        }
        $server['REQUEST_METHOD'] = $laminasRequest->getMethod();

        // Seed headers with HTTP auth information
        $headers = $headers->toArray();
        if (isset($server['PHP_AUTH_USER'])) {
            $headers['PHP_AUTH_USER'] = $server['PHP_AUTH_USER'];
        }
        if (isset($server['PHP_AUTH_PW'])) {
            $headers['PHP_AUTH_PW'] = $server['PHP_AUTH_PW'];
        }

        // Ensure the bodyParams are passed as an array
        $bodyParams = $this->bodyParams() ?: [];

        return new OAuth2Request(
            $laminasRequest->getQuery()->toArray(),
            $bodyParams,
            [], // attributes
            [], // cookies
            [], // files
            $server,
            $laminasRequest->getContent(),
            $headers
        );
    }

    /**
     * Convert the OAuth2 response to a \Laminas\Http\Response
     */
    private function setHttpResponse(OAuth2Response $response): Response
    {
        $httpResponse = $this->getResponse();
        Assert::isInstanceOf($httpResponse, Response::class, 'Cannot convert OAuth2Response to HTTP response');

        $httpResponse->setStatusCode($response->getStatusCode());

        $headers = $httpResponse->getHeaders();
        $headers->addHeaders($response->getHttpHeaders());
        $headers->addHeaderLine('Content-type', 'application/json');

        $httpResponse->setContent($response->getResponseBody());
        return $httpResponse;
    }

    /**
     * Retrieve the OAuth2\Server instance.
     *
     * If not already created by the composed $serverFactory, that callable
     * is invoked with the provided $type as an argument, and the value
     * returned.
     *
     * @param null|string $type
     * @return OAuth2Server
     * @throws RuntimeException If the factory does not return an OAuth2Server instance.
     */
    private function getOAuth2Server($type)
    {
        if ($this->server instanceof OAuth2Server) {
            return $this->server;
        }

        $server = ($this->serverFactory)($type);
        if (! $server instanceof OAuth2Server) {
            throw new RuntimeException(sprintf(
                'OAuth2\Server factory did not return a valid instance; received %s',
                is_object($server) ? $server::class : gettype($server)
            ));
        }
        $this->server = $server;
        return $server;
    }
}
