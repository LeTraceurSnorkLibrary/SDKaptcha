<?php

namespace Tests\ReCaptcha\ReCaptcha;

use DateTime;
use GuzzleHttp\Psr7\Response;
use LeTraceurSnork\Captcha\CaptchaException;
use LeTraceurSnork\UnofficialCaptchaSdk\ReCaptcha\ReCaptcha;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Teapot\StatusCode\Http;

/**
 * @coversDefaultClass \LeTraceurSnork\UnofficialCaptchaSdk\ReCaptcha\ReCaptcha
 */
class VerifyTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::verify
     * @covers ::buildRequest
     */
    public function testVerifyReturnsReCaptchaResponseOnSuccess()
    {
        $mockClient   = $this->createMock(ClientInterface::class);
        $responseJson = json_encode([
            'success'      => true,
            'challenge_ts' => (new DateTime())->format('Y-m-d\TH:i:s\Z'),
            'hostname'     => 'host',
        ]);
        $mockResponse = new Response(Http::OK, [], $responseJson);
        $mockClient->method('sendRequest')->willReturn($mockResponse);

        $captcha = new ReCaptcha('secret', $mockClient);
        $result  = $captcha->verify('token');

        $this->assertTrue($result->isSuccess());
        $this->assertEquals('host', $result->getHostname());
    }

    /**
     * @covers ::__construct
     * @covers ::verify
     */
    public function testVerifyThrowsIfTokenIsEmpty()
    {
        $this->expectException(CaptchaException::class);
        $captcha = new ReCaptcha('secret', $this->createMock(ClientInterface::class));
        $captcha->verify('');
    }

    /**
     * @covers ::__construct
     * @covers ::verify
     * @covers ::buildRequest
     */
    public function testVerifyThrowsCaptchaExceptionOnClientException()
    {
        $mockClient = $this->createMock(ClientInterface::class);
        $mockEx     = $this->createMock(ClientExceptionInterface::class);
        $mockClient->method('sendRequest')->willThrowException($mockEx);

        $captcha = new ReCaptcha('secret', $mockClient);
        $this->expectException(CaptchaException::class);

        $captcha->verify('token');
    }

    /**
     * @covers ::__construct
     * @covers ::verify
     * @covers ::buildRequest
     *
     * @throws Exception
     */
    public function testVerifyThrowsCaptchaExceptionOnParsingError()
    {
        $mockClient = $this->createMock(ClientInterface::class);

        $responseJson = json_encode([
            'hostname' => 'host',
        ]);
        $mockResponse = new Response(Http::OK, [], $responseJson);
        $mockClient->method('sendRequest')->willReturn($mockResponse);

        $captcha = new ReCaptcha('secret', $mockClient);
        $this->expectException(CaptchaException::class);

        $captcha->verify('token');
    }
}
