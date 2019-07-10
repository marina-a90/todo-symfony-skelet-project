<?php

namespace App\Service;

use App\Entity\OAuth\Client;
use OAuth2\IOAuth2GrantUser;
use Symfony\Component\HttpFoundation\Request;
use OAuth2\OAuth2ServerException;
use OAuth2\Model\IOAuth2Client;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class OAuth2
 */
class OAuth2 extends \OAuth2\OAuth2
{
    /**
     * Get the access token from the header
     *
     * @param Request $request
     * @param bool    $removeFromRequest
     *
     * @return string|null
     */
    protected function getBearerTokenFromHeaders(Request $request, $removeFromRequest = false)
    {
        $token = null;

        if ($request->headers->has('X-Auth-Token')) {
            $token = $request->headers->get('X-Auth-Token');
        } else {
            $token = parent::getBearerTokenFromHeaders($request, $removeFromRequest);
        }

        if ($removeFromRequest) {
            if ($request->headers->has('X-Auth-Token')) {
                $request->headers->remove('X-Auth-Token');
            } else {
                $request->headers->remove('AUTHORIZATION');
            }
        }

        return ($token) ? $token : null;
    }

    /**
     * Pull out the Authorization HTTP header and return it.
     * According to draft 20, standard basic authorization is the only
     * header variable required (this does not apply to extended grant types).
     *
     * @param Request $request
     *
     * @return array An array of the basic username and password provided.
     */
    protected function getAuthorizationHeader(Request $request)
    {
        return array(
            'PHP_AUTH_USER' => null,
            'PHP_AUTH_PW' => null
        );
    }

    /**
     * Get client by id
     *
     * @param string $client_id
     *
     * @return \OAuth2\Model\IOAuth2Client
     */
    public function getClient($client_id)
    {
        return $this->storage->getClient($client_id);
    }

    /**
     * Validate client credentials
     *
     * @param Client $client
     * @param string $secret
     *
     * @return bool
     */
    public function validateClientCredentials($client, $secret)
    {
        return $this->storage->checkClientCredentials($client, $secret);
    }

    /**
     * Grant access token.
     *
     * @param Request|null $request
     *
     * @return array
     *
     * @throws OAuth2ServerException
     */
    public function grantAccessToken(Request $request = null)
    {
        $filters = array(
            "grant_type" => array(
                "filter" => FILTER_VALIDATE_REGEXP,
                "options" => array("regexp" => self::GRANT_TYPE_REGEXP),
                "flags" => FILTER_REQUIRE_SCALAR
            ),
            "scope" => array("flags" => FILTER_REQUIRE_SCALAR),
            "code" => array("flags" => FILTER_REQUIRE_SCALAR),
            "redirect_uri" => array("filter" => FILTER_SANITIZE_URL),
            "username" => array("flags" => FILTER_REQUIRE_SCALAR),
            "password" => array("flags" => FILTER_REQUIRE_SCALAR),
            "refresh_token" => array("flags" => FILTER_REQUIRE_SCALAR),
        );

        if ($request === null) {
            $request = Request::createFromGlobals();
        }

        // Input data by default can be either POST or GET
        if ($request->getMethod() === 'POST') {
//            $inputData = $request->request->all();
            $inputData['username'] = $request->query->get('username');
            $inputData['password'] = $request->query->get('password');
            $inputData['client_id'] = $request->query->get('client_id');
            $inputData['client_secret'] = $request->query->get('client_secret');
            $inputData['grant_type'] = $request->query->get('grant_type');
        } else {
            $inputData = $request->query->all();
        }

        // Basic authorization header
        $authHeaders = $this->getAuthorizationHeader($request);

        // Filter input data
        $input = filter_var_array($inputData, $filters);

        // Grant Type must be specified.
        if (!$input["grant_type"]) {
            throw new OAuth2ServerException(self::HTTP_BAD_REQUEST, self::ERROR_INVALID_REQUEST, 'Invalid grant_type parameter or parameter missing');
        }

        // Authorize the client
        $clientCredentials = $this->getClientCredentials($inputData, $authHeaders);

        $client = $this->storage->getClient($clientCredentials[0]);

        if (!$client) {
            throw new OAuth2ServerException(self::HTTP_BAD_REQUEST, self::ERROR_INVALID_CLIENT, 'The client credentials are invalid');
        }

        if ($this->storage->checkClientCredentials($client, $clientCredentials[1]) === false) {
            throw new OAuth2ServerException(self::HTTP_BAD_REQUEST, self::ERROR_INVALID_CLIENT, 'The client credentials are invalid');
        }

        if (!$this->storage->checkRestrictedGrantType($client, $input["grant_type"])) {
            throw new OAuth2ServerException(self::HTTP_BAD_REQUEST, self::ERROR_UNAUTHORIZED_CLIENT, 'The grant type is unauthorized for this client_id');
        }

        // Do the granting
        switch ($input["grant_type"]) {
            case self::GRANT_TYPE_AUTH_CODE:
                // returns array('data' => data, 'scope' => scope)
                $stored = $this->grantAccessTokenAuthCode($client, $input);
                break;
            case self::GRANT_TYPE_USER_CREDENTIALS:
                // returns: true || array('scope' => scope)
                $stored = $this->grantAccessTokenUserCredentials($client, $input);
                break;
            case self::GRANT_TYPE_CLIENT_CREDENTIALS:
                // returns: true || array('scope' => scope)
                $stored = $this->grantAccessTokenClientCredentials($client, $input, $clientCredentials);
                break;
            case self::GRANT_TYPE_REFRESH_TOKEN:
                // returns array('data' => data, 'scope' => scope)
                $stored = $this->grantAccessTokenRefreshToken($client, $input);
                break;
            default:
                if (substr($input["grant_type"], 0, 4) !== 'urn:'
                    && !filter_var($input["grant_type"], FILTER_VALIDATE_URL)
                ) {
                    throw new OAuth2ServerException(
                        self::HTTP_BAD_REQUEST,
                        self::ERROR_INVALID_REQUEST,
                        'Invalid grant_type parameter or parameter missing'
                    );
                }

                // returns: true || array('scope' => scope)
                $stored = $this->grantAccessTokenExtension($client, $inputData, $authHeaders);
        }

        if (!is_array($stored)) {
            $stored = array();
        }

        // if no scope provided to check against $input['scope'] then application defaults are set
        // if no data is provided than null is set
        $stored += array('scope' => $this->getVariable(self::CONFIG_SUPPORTED_SCOPES, null), 'data' => null,
            'access_token_lifetime' => $this->getVariable(self::CONFIG_ACCESS_LIFETIME),
            'issue_refresh_token' => true, 'refresh_token_lifetime' => $this->getVariable(self::CONFIG_REFRESH_LIFETIME));

        $scope = $stored['scope'];
        if ($input["scope"]) {
            // Check scope, if provided
            if (!isset($stored["scope"]) || !$this->checkScope($input["scope"], $stored["scope"])) {
                throw new OAuth2ServerException(self::HTTP_BAD_REQUEST, self::ERROR_INVALID_SCOPE, 'An unsupported scope was requested.');
            }
            $scope = $input["scope"];
        }

        $token = $this->createAccessToken($client, $stored['data'], $scope, $stored['access_token_lifetime'], $stored['issue_refresh_token'], $stored['refresh_token_lifetime']);

        return $token;
    }

    /**
     * @param IOAuth2Client $client
     * @param array         $input
     *
     * @return array|bool
     * @throws OAuth2ServerException
     */
    protected function grantAccessTokenUserCredentials(IOAuth2Client $client, array $input)
    {
        if (!($this->storage instanceof IOAuth2GrantUser)) {
            throw new OAuth2ServerException(Response::HTTP_BAD_REQUEST, self::ERROR_UNSUPPORTED_GRANT_TYPE);
        }

        if (!$input["username"] || !$input["password"]) {
            throw new OAuth2ServerException(Response::HTTP_BAD_REQUEST, self::ERROR_INVALID_REQUEST, 'Missing parameters. "username" and "password" required');
        }

        $stored = $this->storage->checkUserCredentials($client, $input["username"], $input["password"]);

        if ($stored === false) {
            throw new OAuth2ServerException(Response::HTTP_BAD_REQUEST, self::ERROR_INVALID_GRANT, "Sorry, we don't recognize that email/username or password. Please check your login details and try again.");
        }

        return $stored;
    }

}
