<?php

namespace Tests\ReCaptcha\ReCaptchaResponse;

use LeTraceurSnork\Captcha\CaptchaException;
use LeTraceurSnork\UnofficialCaptchaSdk\ReCaptcha\ReCaptchaResponse;
use PHPUnit\Framework\MockObject\Exception as PHPUnitException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Tests\WithFaker;

/**
 * @coversDefaultClass \LeTraceurSnork\UnofficialCaptchaSdk\ReCaptcha\ReCaptchaResponse
 */
class FromHttpResponseTest extends TestCase
{
    use WithFaker;

    /**
     * @covers ::__construct
     * @covers ::fromHttpResponse
     * @covers ::isSuccess
     * @covers ::getErrorCodes
     * @covers ::getHostname
     * @covers ::getChallengeTs
     * @covers ::setErrorCodes
     * @covers ::setHostname
     * @covers ::setChallengeTs
     */
    public function testFromHttpResponseSuccess()
    {
        $hostname     = $this->faker->domainName();
        $challenge_ts = $this->faker->date('c');
        $error_codes  = [$this->faker->randomNumber(), $this->faker->randomNumber()];
        $json         = json_encode([
            'success'      => true,
            'hostname'     => $hostname,
            'challenge_ts' => $challenge_ts,
            'error-codes'  => $error_codes,
        ]);
        $httpResponse = $this->makePsr7Response($json);

        $response = ReCaptchaResponse::fromHttpResponse($httpResponse);

        $this->assertTrue($response->isSuccess());
        $this->assertEquals($hostname, $response->getHostname());
        $this->assertEquals($challenge_ts, $response->getChallengeTs()->format('c'));
        $this->assertEquals($error_codes, $response->getErrorCodes());
    }

    /**
     * @covers ::fromHttpResponse
     * @covers ::isSuccess
     * @covers ::getErrorCodes
     * @covers ::getHostname
     * @covers ::getChallengeTs
     * @covers ::setErrorCodes
     * @covers ::setHostname
     * @covers ::setChallengeTs
     */
    public function testFromHttpResponseWithMinimalFields()
    {
        $json         = json_encode([
            'success' => false,
        ]);
        $httpResponse = $this->makePsr7Response($json);

        $response = ReCaptchaResponse::fromHttpResponse($httpResponse);

        $this->assertFalse($response->isSuccess());
        $this->assertEquals([], $response->getErrorCodes());
        $this->assertNull($response->getHostname());
        $this->assertNull($response->getChallengeTs());
    }

    /**
     * @covers ::fromHttpResponse
     */
    public function testFromHttpResponseThrowsOnMalformedJson()
    {
        $this->expectException(CaptchaException::class);
        $httpResponse = $this->makePsr7Response('{not valid json');

        ReCaptchaResponse::fromHttpResponse($httpResponse);
    }

    /**
     * @covers ::fromHttpResponse
     */
    public function testFromHttpResponseThrowsOnMissingSuccess()
    {
        $this->expectException(CaptchaException::class);
        $httpResponse = $this->makePsr7Response(json_encode([
            'hostname' => 'no success field',
        ]));

        ReCaptchaResponse::fromHttpResponse($httpResponse);
    }

    /**
     * @param string $data
     *
     * @throws PHPUnitException
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
