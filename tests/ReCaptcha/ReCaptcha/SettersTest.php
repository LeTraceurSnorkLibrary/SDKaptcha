<?php

namespace Tests\ReCaptcha\ReCaptcha;

use LeTraceurSnork\UnofficialCaptchaSdk\ReCaptcha\ReCaptcha;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

/**
 * @coversDefaultClass \LeTraceurSnork\UnofficialCaptchaSdk\ReCaptcha\ReCaptcha
 */
class SettersTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::setIp
     * @covers ::buildRequest
     *
     * @throws ReflectionException
     */
    public function testSetIp()
    {
        $captcha = new ReCaptcha('secret');
        $result  = $captcha->setIp('127.0.0.1');

        $this->assertSame($captcha, $result);

        $reflection  = new ReflectionClass($captcha);
        $ip_property = $reflection->getProperty('ip');
        $ip_property->setAccessible(true);
        $this->assertEquals('127.0.0.1', $ip_property->getValue($captcha));

        $buildRequest_method = $reflection->getMethod('buildRequest');
        $buildRequest_method->setAccessible(true);
        $psr7_request = $buildRequest_method->invoke($captcha, 'token');
        parse_str($psr7_request->getBody()->getContents(), $params);
        $this->assertEquals('127.0.0.1', $params['remoteip']);
        $this->assertEquals('secret', $params['secret']);
        $this->assertEquals('token', $params['response']);
    }

    /**
     * @covers ::__construct
     * @covers ::setExpectedAction
     * @covers ::buildRequest
     */
    public function testSetExpectedAction()
    {
        $captcha = new ReCaptcha('secret');
        $result  = $captcha->setExpectedAction('test_action');

        $this->assertSame($captcha, $result);

        $reflection = new ReflectionClass($captcha);
        $property   = $reflection->getProperty('action');
        $property->setAccessible(true);
        $this->assertEquals('test_action', $property->getValue($captcha));

        // Проверяем, что в buildRequest action не добавляется (требуется только для верификации после ответа, не для отправки в запросе)
        $buildRequest_method = $reflection->getMethod('buildRequest');
        $buildRequest_method->setAccessible(true);
        $psr7_request = $buildRequest_method->invoke($captcha, 'token');
        parse_str($psr7_request->getBody()->getContents(), $params);
        $this->assertArrayNotHasKey('action', $params);
    }

    /**
     * @covers ::__construct
     * @covers ::setScoreThreshold
     */
    public function testSetScoreThreshold()
    {
        $captcha = new ReCaptcha('secret');
        $result  = $captcha->setScoreThreshold(0.42);

        $this->assertSame($captcha, $result);

        $reflection = new ReflectionClass($captcha);
        $property   = $reflection->getProperty('scoreThreshold');
        $property->setAccessible(true);
        $this->assertEquals(0.42, $property->getValue($captcha));
    }
}
