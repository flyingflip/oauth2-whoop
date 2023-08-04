<?php

namespace flyingflip\OAuth2\Client\Test\Provider;

use PHPUnit_Framework_TestCase as TestCase;

use flyingflip\OAuth2\Client\Provider\FitbitOptionsProvider;

class WhoopOptionsProviderTest extends TestCase
{
    public function testOptionsHasAuthorizationHeader()
    {
        $clientId = 'client_id';
        $clientSecret = 'client_secret';
        $expected = 'Basic '.base64_encode($clientId . ':' . $clientSecret);

        $options = new FitbitOptionsProvider($clientId, $clientSecret);
        $tokenOptions = $options->getAccessTokenOptions('POST', []);

        $this->assertArrayHasKey('headers', $tokenOptions);
        $this->assertArrayHasKey('Authorization', $tokenOptions['headers']);
        $this->assertEquals($expected, $tokenOptions['headers']['Authorization']);
    }
}
