<?php

namespace SmartCaptcha\SmartCaptcha;

use LeTraceurSnork\UnofficialCaptchaSdk\SmartCaptcha\SmartCaptcha;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @coversDefaultClass \LeTraceurSnork\UnofficialCaptchaSdk\SmartCaptcha\SmartCaptcha
 */
class SetIpTest extends TestCase
{
    /**
     * @covers ::setIp
     */
    public function testSetIp()
    {
        $captcha = new SmartCaptcha('test-secret');
        $result  = $captcha->setIp('3.2.1.0');

        $this->assertSame($captcha, $result);

        $reflection = new ReflectionClass($captcha);
        $property   = $reflection->getProperty('ip');
        $property->setAccessible(true);
        $this->assertEquals('3.2.1.0', $property->getValue($captcha));
    }
}
