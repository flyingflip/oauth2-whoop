<?php

namespace FlyingFlip\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Whoop extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * Whoop API URL.
     *
     * @const string
     */

    const BASE_WHOOP_API_HOSTNAME = 'https://api.prod.whoop.com';

    const BASE_WHOOP_API_URL = 'https://api.prod.whoop.com/oauth';

    /**
     * HTTP header Accept-Language.
     *
     * @const string
     */
    const HEADER_ACCEPT_LANG = 'Accept-Language';

    /**
     * HTTP header Accept-Locale.
     *
     * @const string
     */
    const HEADER_ACCEPT_LOCALE = 'Accept-Locale';

    /**
     * Overridden to inject our options provider
     * @param array $options
     * @param array $collaborators
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        $collaborators['optionProvider'] = new WhoopOptionsProvider(
            $options['clientId'],
            $options['clientSecret']
        );
        parent::__construct($options, $collaborators);
    }

    /**
     * Get authorization url to begin OAuth flow.
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return static::BASE_WHOOP_API_URL . '/oauth2/auth';
    }

    /**
     * Get access token url to retrieve token.
     *
     * @param array $params
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return static::BASE_WHOOP_API_URL . '/oauth2/token';
    }

    /**
     * Returns the url to retrieve the resource owners's profile/details.
     *
     * @param AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return static::BASE_WHOOP_API_HOSTNAME . '/developer/v1/user/profile/basic';
    }

    /**
     * Returns all scopes available from Fitbit.
     * It is recommended you only request the scopes you need!
     *
     * @return array
     */
    protected function getDefaultScopes()
    {
        return ['read:recovery', 'read:cycles', 'read:workout', 'read:sleep', 'read:profile', 'read:body_measurement'];
    }

    /**
     * Checks Fitbit API response for errors.
     *
     * @throws IdentityProviderException
     *
     * @param ResponseInterface $response
     * @param array|string      $data     Parsed response data
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            $errorMessage = '';
            if (!empty($data['errors'])) {
                foreach ($data['errors'] as $error) {
                    if (!empty($errorMessage)) {
                        $errorMessage .= ' , ';
                    }
                    $errorMessage .= implode(' - ', $error);
                }
            } else {
                $errorMessage = $response->getReasonPhrase();
            }
            throw new IdentityProviderException(
                $errorMessage,
                $response->getStatusCode(),
                $response
            );
        }
    }

    /**
     * Returns the string used to separate scopes.
     *
     * @return string
     */
    protected function getScopeSeparator()
    {
        return ' ';
    }

    /**
     * Returns authorization parameters based on provided options.
     * Fitbit does not use the 'approval_prompt' param and here we remove it.
     *
     * @param array $options
     *
     * @return array Authorization parameters
     */
    protected function getAuthorizationParameters(array $options)
    {
        $params = parent::getAuthorizationParameters($options);
        unset($params['approval_prompt']);
        if (!empty($options['prompt'])) {
            $params['prompt'] = $options['prompt'];
        }

        return $params;
    }

    /**
     * Generates a resource owner object from a successful resource owner
     * details request.
     *
     * @param array       $response
     * @param AccessToken $token
     *
     * @return FitbitUser
     */
    public function createResourceOwner(array $response, AccessToken $token)
    {
        return new WhoopUser($response);
    }

    /**
     * Returns the key used in the access token response to identify the resource owner.
     *
     * @return string|null Resource owner identifier key
     */
    protected function getAccessTokenResourceOwnerId()
    {
        return 'user_id';
    }

    public function parseResponse(ResponseInterface $response)
    {
        return parent::parseResponse($response);
    }

    /**
     * Parse Fitbit API Rate Limit headers and return a FitbitRateLimit object.
     *
     * @param ResponseInterface $response
     *
     * @return FitbitRateLimit Fitbit API Rate Limit information
     */
    public function getWhoopRateLimit(ResponseInterface $response)
    {
        return new WhoopRateLimit($response);
    }
}
