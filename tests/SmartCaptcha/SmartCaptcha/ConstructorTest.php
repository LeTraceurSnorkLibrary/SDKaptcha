<?php

namespace Tests\SmartCaptcha\SmartCaptcha;

use GuzzleHttp\Client;
use LeTraceurSnork\Captcha\CaptchaException;
use LeTraceurSnork\SDKaptcha\SmartCaptcha\SmartCaptcha;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use ReflectionClass;

/**
 * @coversDefaultClass \LeTraceurSnork\SDKaptcha\SmartCaptcha\SmartCaptcha
 */
class ConstructorTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testThrowsExceptionWhenServerKeyIsEmpty()
    {
        $this->expectException(CaptchaException::class);
        new SmartCaptcha('');
    }

    /**
     * @covers ::__construct
     */
    public function testSuccess()
    {
        $captcha    = new SmartCaptcha('secret');
        $reflection = new ReflectionClass($captcha);
        $property   = $reflection->getProperty('serverKey');
        $property->setAccessible(true);

        $this->assertEquals('secret', $property->getValue($captcha));
    }

    /**
     * @covers ::__construct
     */
    public function testAcceptsCustomHttpClient()
    {
        $mockClient = $this->createMock(ClientInterface::class);
        $captcha    = new SmartCaptcha('secret', $mockClient);
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
        $captcha    = new SmartCaptcha('secret');
        $reflection = new ReflectionClass($captcha);
        $property   = $reflection->getProperty('httpClient');
        $property->setAccessible(true);

        $httpClient = $property->getValue($captcha);
        $this->assertInstanceOf(Client::class, $httpClient);
    }
}
