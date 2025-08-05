<?php

namespace Tests\SmartCaptcha\SmartCaptchaResponse;

use LeTraceurSnork\SDKaptcha\SmartCaptcha\SmartCaptchaResponse;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * @coversDefaultClass \LeTraceurSnork\SDKaptcha\SmartCaptcha\SmartCaptchaResponse
 */
class FromHttpResponseTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getMessage
     * @covers ::getHost
     * @covers ::isStatusOk
     * @covers ::isSuccess
     * @covers ::fromHttpResponse
     *
     * @throws Exception
     *
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
        $this->assertTrue($response->isStatusOk());
        $this->assertEquals('ok', $response->getMessage());
        $this->assertEquals('testhost', $response->getHost());
    }

    /**
     * @covers ::__construct
     * @covers ::getHost
     * @covers ::isStatusOk
     * @covers ::isSuccess
     * @covers ::fromHttpResponse
     *
     * @throws Exception
     *
     * @return void
     */
    public function testFromHttpResponseEmptyHost()
    {
        $httpResponse = $this->makePsr7Response(json_encode([
            'status'  => 'ok',
            'host'    => '',
        ]));

        $response = SmartCaptchaResponse::fromHttpResponse($httpResponse);

        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isStatusOk());
        $this->assertEquals('', $response->getHost());
    }

    /**
     * @covers ::__construct
     * @covers ::getMessage
     * @covers ::getHost
     * @covers ::isStatusOk
     * @covers ::isSuccess
     * @covers ::fromHttpResponse
     *
     * @throws Exception
     *
     * @return void
     */
    public function testFromHttpResponseSuccessOnlyStatus()
    {
        $httpResponse = $this->makePsr7Response(json_encode([
            'status' => 'ok',
        ]));

        $response = SmartCaptchaResponse::fromHttpResponse($httpResponse);

        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isStatusOk());
        $this->assertEquals('', $response->getMessage());
        $this->assertNull($response->getHost());
    }

    /**
     * @covers ::__construct
     * @covers ::getMessage
     * @covers ::getHost
     * @covers ::isStatusOk
     * @covers ::isSuccess
     * @covers ::fromHttpResponse
     *
     * @throws Exception
     *
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
        $this->assertFalse($response->isStatusOk());
        $this->assertEquals('bad captcha', $response->getMessage());
        $this->assertNull($response->getHost());
    }

    /**
     * @covers ::__construct
     * @covers ::fromHttpResponse
     *
     * @throws Exception
     *
     * @return void
     */
    public function testFromHttpResponseThrowsOnMalformedJson()
    {
        $this->expectException(RuntimeException::class);

        $httpResponse = $this->makePsr7Response('{not valid json');

        SmartCaptchaResponse::fromHttpResponse($httpResponse);
    }

    /**
     * @covers ::__construct
     * @covers ::fromHttpResponse
     *
     * @throws Exception
     *
     * @return void
     */
    public function testFromHttpResponseThrowsOnMissingStatus()
    {
        $this->expectException(RuntimeException::class);

        $httpResponse = $this->makePsr7Response(json_encode([
            'message' => 'we forgot status field',
        ]));

        SmartCaptchaResponse::fromHttpResponse($httpResponse);
    }

    /**
     * @param string $data
     *
     * @throws Exception
     *
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
