<?php

namespace LeTraceurSnork\SDKaptcha\HCaptcha;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use LeTraceurSnork\Captcha\CaptchaException;
use LeTraceurSnork\Captcha\CaptchaVerifierInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use RuntimeException;

/**
 * Captcha-compatible hCaptcha verification service.
 */
class HCaptcha implements CaptchaVerifierInterface
{
    /**
     * HTTP-method to request SITE_VERIFY_URL.
     */
    const HTTP_METHOD = 'POST';

    /**
     * URL to verify captcha's response.
     */
    const SITE_VERIFY_URL = 'https://hcaptcha.com/siteverify';

    /**
     * @var string Secret site key. Required.
     */
    protected $secretKey;

    /**
     * @var string Public site_key. Optional.
     */
    protected $siteKey;

    /**
     * @var string|null User's IP. Optional.
     */
    protected $ip;

    /**
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * @param string               $secretKey
     * @param ClientInterface|null $client
     */
    public function __construct($secretKey, ClientInterface $client = null)
    {
        if (empty($secretKey)) {
            throw new CaptchaException('hCaptcha secret cannot be empty.');
        }

        $this->secretKey  = $secretKey;
        $this->httpClient = $client
            ?: new Client([
                'timeout'         => 5,
                'connect_timeout' => 5,
            ]);
    }

    /**
     * @param $siteKey
     *
     * @return $this
     */
    public function setSiteKey($siteKey)
    {
        $this->siteKey = $siteKey;

        return $this;
    }

    /**
     * @param $ip
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
     *
     * @return HCaptchaResponse
     */
    public function verify($token)
    {
        if (empty($token)) {
            throw new CaptchaException('hCaptcha token cannot be empty.');
        }

        try {
            $request  = $this->buildRequest($token);
            $response = $this->httpClient->sendRequest($request);

            return HCaptchaResponse::fromHttpResponse($response);
        } catch (ClientExceptionInterface $e) {
            throw new CaptchaException('hCaptcha HTTP error: ' . $e->getMessage(), 0, $e);
        } catch (RuntimeException $e) {
            throw new CaptchaException('hCaptcha parse error: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param $token
     *
     * @return Request
     */
    protected function buildRequest($token)
    {
        $body = [
            'secret'   => $this->secretKey,
            'response' => $token,
        ];
        if ($this->ip) {
            $body['remoteip'] = $this->ip;
        }
        if ($this->siteKey) {
            $body['sitekey'] = $this->siteKey;
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
