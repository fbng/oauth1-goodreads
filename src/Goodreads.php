<?php

namespace NetGalley\OAuth1\Client\Server;

use League\OAuth1\Client\Credentials\CredentialsException;
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
    protected function createTemporaryCredentials($body)
    {
        // satisfying parent requirement for an oauth_callback_confirmed
        // argument, which Goodreads doesn't return
        $body .= '&oauth_callback_confirmed=true';

        return parent::createTemporaryCredentials($body);
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthorizationUrl($temporaryIdentifier, array $options = [])
    {
        $url = parent::getAuthorizationUrl($temporaryIdentifier, $options);

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
    public function urlAuthorization()
    {
        return self::API_URL . 'oauth/authorize';
    }

    /**
     * {@inheritDoc}
     */
    public function urlTemporaryCredentials()
    {
        return self::API_URL . 'oauth/request_token';
    }

    /**
     * {@inheritDoc}
     */
    public function urlTokenCredentials()
    {
        return self::API_URL . 'oauth/access_token';
    }

    /**
     * {@inheritDoc}
     */
    public function urlUserDetails()
    {
        return self::API_URL . 'api/auth_user';
    }

    /**
     * {@inheritDoc}
     */
    public function userDetails($data, TokenCredentials $tokenCredentials)
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
    public function userEmail($data, TokenCredentials $tokenCredentials)
    {
        return;
    }

    /**
     * {@inheritDoc}
     */
    public function userScreenName($data, TokenCredentials $tokenCredentials)
    {
        return;
    }

    /**
     * {@inheritDoc}
     */
    public function userUid($data, TokenCredentials $tokenCredentials)
    {
        return (int) $data->user->attributes()['id'];
    }
}
