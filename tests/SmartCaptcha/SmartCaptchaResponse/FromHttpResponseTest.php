<?php

namespace Tests\SmartCaptcha\SmartCaptchaResponse;

use LeTraceurSnork\UnofficialCaptchaSdk\SmartCaptcha\SmartCaptchaResponse;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @coversDefaultClass \LeTraceurSnork\UnofficialCaptchaSdk\SmartCaptcha\SmartCaptchaResponse
 */
class FromHttpResponseTest extends TestCase
{
    /**
     * @covers ::fromHttpResponse
     *
     * @throws Exception
     * @return void
     */
    public function testFromHttpResponseSuccess()
    {
        $httpResponse = $this->makePsr7Response(json_encode([
            'status'  => 'ok',
            'message' => 'ok',
            'host'    => 'testhost',
        ]));

        $response = SmartCaptchaResponse::fromHttpResponse($httpResponse);

        $this->assertTrue($response->isSuccess());
        $this->assertEquals('ok', $response->getMessage());
        $this->assertEquals('testhost', $response->getHost());
    }

    /**
     * @covers ::fromHttpResponse
     *
     * @throws Exception
     * @return void
     */
    public function testFromHttpResponseFail()
    {
        $httpResponse = $this->makePsr7Response(json_encode([
            'status'  => 'failed',
            'message' => 'bad captcha',
            'host'    => null,
        ]));

        $response = SmartCaptchaResponse::fromHttpResponse($httpResponse);

        $this->assertFalse($response->isSuccess());
        $this->assertEquals('bad captcha', $response->getMessage());
        $this->assertNull($response->getHost());
    }

    /**
     * @covers ::fromHttpResponse
     *
     * @throws Exception
     * @return void
     */
    public function testFromHttpResponseThrowsOnMalformedJson()
    {
        $this->expectException(\RuntimeException::class);

        $httpResponse = $this->makePsr7Response('{not valid json');

        SmartCaptchaResponse::fromHttpResponse($httpResponse);
    }

    /**
     * @covers ::fromHttpResponse
     *
     * @throws Exception
     * @return void
     */
    public function testFromHttpResponseThrowsOnMissingStatus()
    {
        $this->expectException(\RuntimeException::class);

        $httpResponse = $this->makePsr7Response(json_encode([
            'message' => 'we forgot status field',
        ]));

        SmartCaptchaResponse::fromHttpResponse($httpResponse);
    }

    /**
     * @throws Exception
     * @return ResponseInterface
     */
    private function makePsr7Response($data)
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn($data);

        $mock = $this->createMock(ResponseInterface::class);
        $mock->method('getBody')->willReturn($stream);

        return $mock;
    }
}
