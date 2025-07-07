<?php

namespace HCaptcha\HCaptcha;

use LeTraceurSnork\UnofficialCaptchaSdk\HCaptcha\HCaptcha;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

/**
 * @coversDefaultClass \LeTraceurSnork\UnofficialCaptchaSdk\HCaptcha\HCaptcha
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
        $captcha = new HCaptcha('secret');
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
     * @covers ::setSiteKey
     * @covers ::buildRequest
     */
    public function testSetSiteKey()
    {
        $captcha = new HCaptcha('secret');
        $result  = $captcha->setSiteKey('test_siteKey');

        $this->assertSame($captcha, $result);

        $reflection = new ReflectionClass($captcha);
        $property   = $reflection->getProperty('siteKey');
        $property->setAccessible(true);
        $this->assertEquals('test_siteKey', $property->getValue($captcha));

        $buildRequest_method = $reflection->getMethod('buildRequest');
        $buildRequest_method->setAccessible(true);
        $psr7_request = $buildRequest_method->invoke($captcha, 'token');
        parse_str($psr7_request->getBody()->getContents(), $params);
        $this->assertEquals('test_siteKey', $params['sitekey']);
        $this->assertEquals('secret', $params['secret']);
        $this->assertEquals('token', $params['response']);
    }
}
