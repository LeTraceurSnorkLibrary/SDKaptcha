<?php

namespace Tests\HCaptcha\HCaptchaResponse;

use LeTraceurSnork\Captcha\CaptchaException;
use LeTraceurSnork\UnofficialCaptchaSdk\HCaptcha\HCaptchaResponse;
use PHPUnit\Framework\MockObject\Exception as PHPUnitException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Tests\WithFaker;

/**
 * @coversDefaultClass \LeTraceurSnork\UnofficialCaptchaSdk\HCaptcha\HCaptchaResponse
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
     * @covers ::getChallengeTimestamp
     * @covers ::getCredit
     * @covers ::getScoreReason
     * @covers ::getScore
     * @covers ::setErrorCodes
     * @covers ::setHostname
     * @covers ::setChallengeTimestamp
     * @covers ::setCredit
     * @covers ::setScoreReason
     * @covers ::setScore
     */
    public function testFromHttpResponseSuccess()
    {
        $hostname     = $this->faker->domainName();
        $challenge_ts = $this->faker->date('c');
        $credit       = $this->faker->boolean();
        $error_codes  = [$this->faker->randomNumber(), $this->faker->randomNumber()];
        $score        = $this->faker->randomFloat(2, 0, 1);
        $score_reason = [$this->faker->word(), $this->faker->word()];
        $json         = json_encode([
            'success'      => true,
            'hostname'     => $hostname,
            'challenge_ts' => $challenge_ts,
            'credit'       => $credit,
            'error-codes'  => $error_codes,
            'score'        => $score,
            'score_reason' => $score_reason,
        ]);
        $httpResponse = $this->makePsr7Response($json);

        $response = HCaptchaResponse::fromHttpResponse($httpResponse);

        $this->assertTrue($response->isSuccess());
        $this->assertEquals($hostname, $response->getHostname());
        $this->assertEquals($challenge_ts, $response->getChallengeTimestamp()->format('c'));
        $this->assertEquals($credit, $response->getCredit());
        $this->assertEquals($error_codes, $response->getErrorCodes());
        $this->assertEquals($score, $response->getScore());
        $this->assertEquals($score_reason, $response->getScoreReason());
    }

    /**
     * @covers ::fromHttpResponse
     * @covers ::isSuccess
     * @covers ::getErrorCodes
     * @covers ::getHostname
     * @covers ::getChallengeTimestamp
     * @covers ::getCredit
     * @covers ::getScoreReason
     * @covers ::getScore
     * @covers ::setErrorCodes
     * @covers ::setHostname
     * @covers ::setChallengeTimestamp
     * @covers ::setCredit
     * @covers ::setScoreReason
     * @covers ::setScore
     */
    public function testFromHttpResponseWithMinimalFields()
    {
        $json         = json_encode([
            'success' => false,
        ]);
        $httpResponse = $this->makePsr7Response($json);

        $response = HCaptchaResponse::fromHttpResponse($httpResponse);

        $this->assertFalse($response->isSuccess());
        $this->assertEquals([], $response->getErrorCodes());
        $this->assertNull($response->getHostname());
        $this->assertNull($response->getChallengeTimestamp());
        $this->assertNull($response->getCredit());
        $this->assertNull($response->getScore());
        $this->assertNull($response->getScoreReason());
    }

    /**
     * @covers ::fromHttpResponse
     */
    public function testFromHttpResponseThrowsOnMalformedJson()
    {
        $this->expectException(CaptchaException::class);
        $httpResponse = $this->makePsr7Response('{not valid json');

        HCaptchaResponse::fromHttpResponse($httpResponse);
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

        HCaptchaResponse::fromHttpResponse($httpResponse);
    }

    /**
     * @param string $data
     *
     * @throws PHPUnitException
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
