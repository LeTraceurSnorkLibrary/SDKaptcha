<?php

namespace Tests\ReCaptcha\ReCaptcha;

use GuzzleHttp\Client;
use LeTraceurSnork\Captcha\CaptchaException;
use LeTraceurSnork\SDKaptcha\ReCaptcha\ReCaptcha;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use ReflectionClass;

/**
 * @coversDefaultClass \LeTraceurSnork\SDKaptcha\ReCaptcha\ReCaptcha
 */
class ConstructorTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testThrowsExceptionWhenSecretKeyIsEmpty()
    {
        $this->expectException(CaptchaException::class);
        new ReCaptcha('');
    }

    /**
     * @covers ::__construct
     */
    public function testSuccess()
    {
        $captcha    = new ReCaptcha('secret');
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
        $captcha    = new ReCaptcha('secret', $mockClient);
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
        $captcha    = new ReCaptcha('secret');
        $reflection = new ReflectionClass($captcha);
        $property   = $reflection->getProperty('httpClient');
        $property->setAccessible(true);

        $httpClient = $property->getValue($captcha);
        $this->assertInstanceOf(Client::class, $httpClient);
    }
}
