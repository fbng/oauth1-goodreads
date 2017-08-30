<?php

namespace NetGalley\OAuth1\Client\Tests\Server;

use League\OAuth1\Client\Credentials\TemporaryCredentials;
use League\OAuth1\Client\Credentials\TokenCredentials;
use NetGalley\OAuth1\Client\Server\Goodreads;

/**
 * Exercises the OAuth1 client for Goodreads.
 */
class GoodreadsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
    }

    /**
     * Configure user data as returned by Server::fetchUserDetails().
     *
     * @return \SimpleXMLElement
     */
    protected function configureUserData()
    {
        return simplexml_load_string(
'<?xml version="1.0" encoding="UTF-8"?>
<GoodreadsResponse>
  <Request>
    <authentication>true</authentication>
      <key><![CDATA[TestClientCredentialsKey]]></key>
    <method><![CDATA[api_auth_user]]></method>
  </Request>
  <user id="12345678">
  <name>Test User</name>
  <link><![CDATA[https://www.goodreads.com/user/show/12345678-test?utm_medium=api]]></link>
</user>

</GoodreadsResponse>'
        );
    }

    /**
     * Verify that temporary credentials can be created from a standard
     * Goodreads response to a temporary token request.
     */
    public function testCreateTemporaryCredentials()
    {
        $client = new Goodreads(array(
            'identifier' => 'test-api-key',
            'secret' => 'test-api-secret'
        ));

        $reflectionMethod = new \ReflectionMethod($client, 'createTemporaryCredentials');
        $reflectionMethod->setAccessible(true);

        // response body does not include an oauth_callback_confirmed argument
        $temporaryCredentials = $reflectionMethod->invoke(
            $client,
            'oauth_token=test-temporary-oauth-token&oauth_token_secret=test-temporary-oauth-secret'
        );

        $this->assertInstanceOf(TemporaryCredentials::class, $temporaryCredentials);
        $this->assertSame('test-temporary-oauth-token', $temporaryCredentials->getIdentifier());
        $this->assertSame('test-temporary-oauth-secret', $temporaryCredentials->getSecret());
    }

    /**
     * Verify that an authorization URL can be generated without a callback URL.
     */
    public function testGetAuthorizationUrlWithoutCallback()
    {
        $client = new Goodreads(array(
            'identifier' => 'test-api-key',
            'secret' => 'test-api-secret'
        ));

        $this->assertSame(
            'https://www.goodreads.com/oauth/authorize?oauth_token=test-temporary-oauth-token',
            $client->getAuthorizationUrl('test-temporary-oauth-token')
        );
    }

    /**
     * Verify that an authorization URL can be generated with a callback URL.
     */
    public function testGetAuthorizationUrlWithCallback()
    {
        $client = new Goodreads(array(
            'identifier' => 'test-api-key',
            'secret' => 'test-api-secret',
            'callback_uri' => 'http://test/callback/url'
        ));

        $this->assertSame(
            'https://www.goodreads.com/oauth/authorize?oauth_token=test-temporary-oauth-token&oauth_callback=http%3A%2F%2Ftest%2Fcallback%2Furl',
            $client->getAuthorizationUrl('test-temporary-oauth-token')
        );
    }

    /**
     * Verfiy that the user ID can be parsed from the fetched user details.
     */
    public function testUserUid()
    {
        $client = new Goodreads(array(
            'identifier' => 'test-api-key',
            'secret' => 'test-api-secret'
        ));

        $this->assertSame(12345678, $client->userUid($this->configureUserData(), new TokenCredentials()));
    }

    /**
     * Verfiy that the user details can be transported in a User entity.
     */
    public function testUserDetails()
    {
        $client = new Goodreads(array(
            'identifier' => 'test-api-key',
            'secret' => 'test-api-secret'
        ));

        $user = $client->userDetails($this->configureUserData(), new TokenCredentials());

        $this->assertSame(12345678, $user->uid);
        $this->assertSame('Test User', $user->name);
        $this->assertSame(
            array('profile' => 'https://www.goodreads.com/user/show/12345678-test?utm_medium=api'),
            $user->urls
        );
    }
}
