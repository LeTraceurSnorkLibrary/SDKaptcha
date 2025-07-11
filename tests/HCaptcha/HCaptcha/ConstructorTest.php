<?php

namespace Tests\HCaptcha\HCaptcha;

use GuzzleHttp\Client;
use LeTraceurSnork\Captcha\CaptchaException;
use LeTraceurSnork\SDKaptcha\HCaptcha\HCaptcha;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use ReflectionClass;

/**
 * @coversDefaultClass \LeTraceurSnork\SDKaptcha\HCaptcha\HCaptcha
 */
class ConstructorTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testThrowsExceptionWhenServerKeyIsEmpty()
    {
        $this->expectException(CaptchaException::class);
        new HCaptcha('');
    }

    /**
     * @covers ::__construct
     */
    public function testSuccess()
    {
        $captcha    = new HCaptcha('secret');
        $reflection = new ReflectionClass($captcha);
        $property   = $reflection->getProperty('secretKey');
        $property->setAccessible(true);

        $this->assertEquals('secret', $property->getValue($captcha));
    }

    /**
     * @covers ::__construct
     */
    public function testAcceptsCustomHttpClient()
    {
        $mockClient = $this->createMock(ClientInterface::class);
        $captcha    = new HCaptcha('secret', $mockClient);
        $reflection = new ReflectionClass($captcha);
        $property   = $reflection->getProperty('httpClient');
        $property->setAccessible(true);

        $this->assertSame($mockClient, $property->getValue($captcha));
    }

    /**
     * @covers ::__construct
     */
    public function testDefaultHttpClientIsGuzzle()
    {
        $captcha    = new HCaptcha('secret');
        $reflection = new ReflectionClass($captcha);
        $property   = $reflection->getProperty('httpClient');
        $property->setAccessible(true);

        $httpClient = $property->getValue($captcha);
        $this->assertInstanceOf(Client::class, $httpClient);
    }
}
