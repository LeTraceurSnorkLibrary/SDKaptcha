<?php

namespace LeTraceurSnork\SDKaptcha\SmartCaptcha;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use LeTraceurSnork\Captcha\CaptchaException;
use LeTraceurSnork\Captcha\CaptchaVerifierInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use RuntimeException;

class SmartCaptcha implements CaptchaVerifierInterface
{
    /**
     * HTTP-method to request SITE_VERIFY_URL.
     */
    const HTTP_METHOD = 'POST';

    /**
     * URL to verify captcha's response.
     */
    const SITE_VERIFY_URL = 'https://smartcaptcha.yandexcloud.net/validate';

    /**
     * @var string SmartCaptcha secret serverKey
     */
    protected $serverKey;

    /**
     * @var string|null User's IP
     */
    protected $ip;

    /**
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * @param string               $serverKey SmartCaptcha secret serverKey
     * @param ClientInterface|null $client    PSR-18 compatible HTTP Client
     */
    public function __construct($serverKey, ClientInterface $client = null)
    {
        if (empty($serverKey)) {
            throw new CaptchaException('SmartCaptcha secret cannot be empty.');
        }

        $this->serverKey  = $serverKey;
        $this->httpClient = isset($client)
            ? $client
            : new Client([
                'timeout'         => 5,
                'connect_timeout' => 5,
            ]);
    }

    /**
     * Set user's IP.
     *
     * @param string $ip
     *
     * @return $this
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function verify($token)
    {
        if (empty($token)) {
            throw new RuntimeException('SmartCaptcha token cannot be empty.');
        }

        try {
            $request  = $this->buildRequest($token);
            $response = $this->httpClient->sendRequest($request);

            return SmartCaptchaResponse::fromHttpResponse($response);
        } catch (ClientExceptionInterface $e) {
            throw new CaptchaException('SmartCaptcha HTTP error: ' . $e->getMessage(), 0, $e);
        } catch (RuntimeException $e) {
            throw new CaptchaException('SmartCaptcha parse error: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Builds PSR-7 compatible HTTP-Request.
     *
     * @param string $token
     *
     * @return RequestInterface
     */
    protected function buildRequest($token)
    {
        $body = [
            'secret' => $this->serverKey,
            'token'  => $token,
        ];
        if (isset($this->ip)) {
            $body['ip'] = $this->ip;
        }

        return new Request(
            static::HTTP_METHOD,
            static::SITE_VERIFY_URL,
            [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            http_build_query($body)
        );
    }
}
