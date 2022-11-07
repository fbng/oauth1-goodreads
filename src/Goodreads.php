<?php

namespace NetGalley\OAuth1\Client\Server;

use League\OAuth1\Client\Credentials\CredentialsException;
use League\OAuth1\Client\Credentials\TemporaryCredentials;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Server\Server;
use League\OAuth1\Client\Server\User;

/**
 * OAuth1 client for Goodreads.
 */
class Goodreads extends Server
{
    /**
     * @var string
     */
    const API_URL = 'https://www.goodreads.com/';

    /**
     * @var string
     */
    protected $responseType = 'xml';

    /**
     * {@inheritDoc}
     */
    protected function createTemporaryCredentials($body): TemporaryCredentials
    {
        // satisfying parent requirement for an oauth_callback_confirmed
        // argument, which Goodreads doesn't return
        $body .= '&oauth_callback_confirmed=true';

        return parent::createTemporaryCredentials($body);
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthorizationUrl($temporaryIdentifier, array $options = []): string
    {
        $url = parent::getAuthorizationUrl($temporaryIdentifier);

        if (!$this->clientCredentials->getCallbackUri()) {
            return $url;
        }

        // add callback URL to the request query from where Goodreads gets it
        // instead of from the header parameters
        $queryString = http_build_query(array('oauth_callback' => $this->clientCredentials->getCallbackUri()));

        return $this->buildUrl($url, $queryString);
    }

    /**
     * {@inheritDoc}
     */
    public function urlAuthorization(): string
    {
        return self::API_URL . 'oauth/authorize';
    }

    /**
     * {@inheritDoc}
     */
    public function urlTemporaryCredentials(): string
    {
        return self::API_URL . 'oauth/request_token';
    }

    /**
     * {@inheritDoc}
     */
    public function urlTokenCredentials(): string
    {
        return self::API_URL . 'oauth/access_token';
    }

    /**
     * {@inheritDoc}
     */
    public function urlUserDetails(): string
    {
        return self::API_URL . 'api/auth_user';
    }

    /**
     * {@inheritDoc}
     */
    public function userDetails($data, TokenCredentials $tokenCredentials): User
    {
        $user = new User();

        $user->uid = $this->userUid($data, $tokenCredentials);
        $user->name = (string) $data->user->name;
        $user->urls = array('profile' => (string) $data->user->link);

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function userEmail($data, TokenCredentials $tokenCredentials): ?string
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function userScreenName($data, TokenCredentials $tokenCredentials): ?string
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function userUid($data, TokenCredentials $tokenCredentials): string|int
    {
        return (int) $data->user->attributes()['id'];
    }
}
