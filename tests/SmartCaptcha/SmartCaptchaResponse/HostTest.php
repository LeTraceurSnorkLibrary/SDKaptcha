<?php

namespace Tests\SmartCaptcha\SmartCaptchaResponse;

use LeTraceurSnork\UnofficialCaptchaSdk\SmartCaptcha\SmartCaptchaResponse;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \LeTraceurSnork\UnofficialCaptchaSdk\SmartCaptcha\SmartCaptchaResponse
 */
class HostTest extends TestCase
{
    /**
     * @covers ::getHost
     */
    public function testGetHostReturnsNullIfNotSet()
    {
        $response = new SmartCaptchaResponse(true, 'irrelevant');
        $this->assertNull($response->getHost());
    }

    /**
     * @covers ::setHost
     * @covers ::getHost
     */
    public function testSetHostAssignsHostAndGetHostReturnsIt()
    {
        $response = new SmartCaptchaResponse(true, 'irrelevant');
        $result   = $response->setHost('test.host');

        $this->assertSame($response, $result, 'setHost should return $this');
        $this->assertEquals('test.host', $response->getHost());
    }
}
