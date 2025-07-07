<?php

namespace HCaptcha\HCaptcha;

use DateTime;
use GuzzleHttp\Psr7\Response;
use LeTraceurSnork\Captcha\CaptchaException;
use LeTraceurSnork\UnofficialCaptchaSdk\HCaptcha\HCaptcha;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Teapot\StatusCode\Http;

/**
 * @coversDefaultClass \LeTraceurSnork\UnofficialCaptchaSdk\HCaptcha\HCaptcha
 */
class VerifyTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::verify
     * @covers ::buildRequest
     */
    public function testVerifyReturnsHCaptchaResponseOnSuccess()
    {
        $mockClient   = $this->createMock(ClientInterface::class);
        $responseJson = json_encode([
            'success'      => true,
            'challenge_ts' => (new DateTime())->format('Y-m-dTH:i:s'),
            'hostname'     => 'host',
        ]);
        $mockResponse = new Response(Http::OK, [], $responseJson);
        $mockClient->method('sendRequest')->willReturn($mockResponse);

        $captcha = new HCaptcha('secret', $mockClient);
        $result  = $captcha->verify('token');

        $this->assertTrue($result->isSuccess());
        $this->assertEquals('host', $result->getHost());
    }

    /**
     * @covers ::__construct
     * @covers ::verify
     */
    public function testVerifyThrowsIfTokenIsEmpty()
    {
        $this->expectException(CaptchaException::class);
        $captcha = new HCaptcha('secret', $this->createMock(ClientInterface::class));
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

        $captcha = new HCaptcha('secret', $mockClient);
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

        $captcha = new HCaptcha('secret', $mockClient);
        $this->expectException(CaptchaException::class);

        $captcha->verify('token');
    }
}
