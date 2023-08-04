<?php

namespace FlyingFlip\OAuth2\Client\Test\Provider;

use PHPUnit_Framework_TestCase as TestCase;

use FlyingFlip\OAuth2\Client\Provider\WoopOptionsProvider;

class WhoopOptionsProviderTest extends TestCase
{
    public function testOptionsHasAuthorizationHeader()
    {
        $clientId = 'client_id';
        $clientSecret = 'client_secret';
        $expected = 'Basic '.base64_encode($clientId . ':' . $clientSecret);

        $options = new WhoopOptionsProvider($clientId, $clientSecret);
        $tokenOptions = $options->getAccessTokenOptions('POST', []);

        $this->assertArrayHasKey('headers', $tokenOptions);
        $this->assertArrayHasKey('Authorization', $tokenOptions['headers']);
        $this->assertEquals($expected, $tokenOptions['headers']['Authorization']);
    }
}
