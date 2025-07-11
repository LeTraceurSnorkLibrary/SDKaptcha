<?php

namespace LeTraceurSnork\UnofficialCaptchaSdk\ReCaptcha;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use LeTraceurSnork\Captcha\CaptchaException;
use LeTraceurSnork\Captcha\CaptchaVerifierInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use RuntimeException;

/**
 * Google reCAPTCHA captcha verifier.
 *
 * @link https://developers.google.com/recaptcha/docs/verify
 */
class ReCaptcha implements CaptchaVerifierInterface
{
    const HTTP_METHOD = 'POST';
    const SITE_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * @var string Google ReCaptcha secret server key
     */
    protected $secretKey;

    /**
     * @var string|null User's IP
     */
    protected $ip;

    /**
     * @var string|null Required `action`, for v3 reCAPTCHA
     */
    protected $action;

    /**
     * @var float|null Score threshold (v3)
     */
    protected $scoreThreshold;

    /**
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * @param string               $secretKey Google ReCaptcha secret key
     * @param ClientInterface|null $client    PSR-18 compatible HTTP client
     */
    public function __construct($secretKey, ClientInterface $client = null)
    {
        if (empty($secretKey)) {
            throw new CaptchaException('Google ReCaptcha secret cannot be empty.');
        }

        $this->secretKey  = $secretKey;
        $this->httpClient = isset($client)
            ? $client
            : new Client([
                'timeout' => 5,
                'connect_timeout' => 5,
            ]);
    }

    /**
     * Set user's IP.
     *
     * @param string $ip
     * @return $this
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * Set expected action (v3 only).
     *
     * @param string $action
     * @return $this
     */
    public function setExpectedAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Set expected score threshold (v3 only, 0..1).
     *
     * @param float $score
     * @return $this
     */
    public function setScoreThreshold($score)
    {
        $this->scoreThreshold = (float)$score;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function verify($token)
    {
        if (empty($token)) {
            throw new RuntimeException('Google ReCaptcha token cannot be empty.');
        }

        try {
            $request  = $this->buildRequest($token);
            $response = $this->httpClient->sendRequest($request);
            $body = (string)$response->getBody();
            $result = json_decode($body, true);

            if (!is_array($result)) {
                throw new CaptchaException('ReCaptcha: Invalid API response format');
            }

            // Базовая проверка
            if (empty($result['success'])) {
                throw new CaptchaException('ReCaptcha: Verification failed' . (isset($result['error-codes']) ? (' (' . implode(',', $result['error-codes']) . ')') : ''));
            }

            // Если выставлены action / threshold (reCAPTCHA v3)
            if ($this->action !== null && isset($result['action']) && $result['action'] !== $this->action) {
                throw new CaptchaException('ReCaptcha: Action mismatch (expected: ' . $this->action . ', got: ' . $result['action'] . ')');
            }

            if ($this->scoreThreshold !== null && isset($result['score']) && $result['score'] < $this->scoreThreshold) {
                throw new CaptchaException('ReCaptcha: Score threshold not met (minimum: ' . $this->scoreThreshold . ', got: ' . $result['score'] . ')');
            }

            // Возвращает массив (или по месту реализовать ваш интерфейс CaptchaResponseInterface)
            return $result;
        } catch (ClientExceptionInterface $e) {
            throw new CaptchaException('ReCaptcha HTTP error: ' . $e->getMessage(), 0, $e);
        } catch (RuntimeException $e) {
            throw new CaptchaException('ReCaptcha parse error: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Builds PSR-7 compatible HTTP-Request.
     *
     * @param string $token
     * @return RequestInterface
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
