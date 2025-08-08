<?php

namespace Tests\SmartCaptcha\SmartCaptcha;

use GuzzleHttp\Psr7\Response;
use LeTraceurSnork\Captcha\CaptchaException;
use LeTraceurSnork\SDKaptcha\SmartCaptcha\SmartCaptcha;
use LeTraceurSnork\SDKaptcha\SmartCaptcha\SmartCaptchaResponse;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Teapot\StatusCode\Http;

/**
 * @coversDefaultClass \LeTraceurSnork\SDKaptcha\SmartCaptcha\SmartCaptcha
 */
class VerifyTest extends TestCase
{
    /**
     * @covers ::verify
     * @covers ::buildRequest
     */
    public function testVerifyReturnsCaptchaResponseOnSuccess()
    {
        $mockClient   = $this->createMock(ClientInterface::class);
        $responseJson = json_encode([
            'status'  => 'ok',
            'message' => 'ok',
            'host'    => 'host',
        ]);
        $mockResponse = new Response(Http::OK, [], $responseJson);

        $mockClient->method('sendRequest')->willReturn($mockResponse);

        $captcha = (new SmartCaptcha('testkey', $mockClient))
            ->setIp('0.0.0.0');
        $result  = $captcha->verify('token');

        $this->assertInstanceOf(SmartCaptchaResponse::class, $result);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals('ok', $result->getMessage());
        $this->assertEquals('host', $result->getHost());
    }

    /**
     * @covers ::verify
     */
    public function testVerifyThrowsIfTokenIsEmpty()
    {
        $this->expectException(CaptchaException::class);
        $captcha = new SmartCaptcha('key', $this->createMock(ClientInterface::class));
        $captcha->verify('');
    }

    /**
     * @covers ::verify
     * @covers ::buildRequest
     */
    public function testVerifyThrowsCaptchaExceptionOnClientException()
    {
        $mockClient = $this->createMock(ClientInterface::class);
        $mockEx     = $this->createMock(ClientExceptionInterface::class);
        $mockClient->method('sendRequest')->willThrowException($mockEx);

        $captcha = new SmartCaptcha('secret', $mockClient);
        $this->expectException(CaptchaException::class);

        $captcha->verify('token');
    }

    /**
     * @covers ::verify
     * @covers ::buildRequest
     *
     * @throws Exception
     */
    public function testVerifyThrowsCaptchaExceptionOnParsingError()
    {
        $mockClient   = $this->createMock(ClientInterface::class);
        $responseJson = json_encode([
            'message' => 'no status field',
        ]);
        $mockResponse = new Response(Http::OK, [], $responseJson);
        $mockClient->method('sendRequest')->willReturn($mockResponse);

        $captcha = new SmartCaptcha('secret', $mockClient);
        $this->expectException(CaptchaException::class);

        $captcha->verify('token');
    }
}
