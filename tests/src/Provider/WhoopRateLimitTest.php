<?php

namespace FlyingFlip\OAuth2\Client\Test\Provider;

use FlyingFlip\OAuth2\Client\Provider\Whoop;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Http\Message\ResponseInterface;

class WhoopRateLimitTest extends TestCase
{
    protected $provider;

    protected function setUp()
    {
        $this->provider = new Whoop([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ]);
    }

    public function tearDown()
    {
        \Mockery::close();
        parent::tearDown();
    }


    public function testGetWhoopRateLimit()
    {
        $response = \Mockery::mock(ResponseInterface::class)->makePartial();
        $response->shouldReceive('getStatusCode')->andReturn(429);
        $response->shouldReceive('getHeader')->with('Retry-After')->andReturn(['1234']);
        $response->shouldReceive('getHeader')->with('Whoop-Rate-Limit-Limit')->andReturn(['150']);
        $response->shouldReceive('getHeader')->with('Whoop-Rate-Limit-Remaining')->andReturn(['100']);
        $response->shouldReceive('getHeader')->with('Whoop-Rate-Limit-Reset')->andReturn(['2345']);
        $rateLimit = $this->provider->getWhoopRateLimit($response);
        $this->assertEquals('1234', $rateLimit->getRetryAfter());
        $this->assertEquals('150', $rateLimit->getLimit());
        $this->assertEquals('100', $rateLimit->getRemaining());
        $this->assertEquals('2345', $rateLimit->getReset());
    }

    public function testGetWhoopRateLimitMissingHeaders()
    {
        $response = \Mockery::mock(ResponseInterface::class)->makePartial();
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('getHeader')->with('Whoop-Rate-Limit-Limit')->andReturn(null);
        $response->shouldReceive('getHeader')->with('Whoop-Rate-Limit-Remaining')->andReturn(null);
        $response->shouldReceive('getHeader')->with('Whoop-Rate-Limit-Reset')->andReturn(null);
        $rateLimit = $this->provider->getWhoopRateLimit($response);
        $this->assertNull($rateLimit->getRetryAfter());
        $this->assertNull($rateLimit->getLimit());
        $this->assertNull($rateLimit->getRemaining());
        $this->assertNull($rateLimit->getReset());
    }
}
