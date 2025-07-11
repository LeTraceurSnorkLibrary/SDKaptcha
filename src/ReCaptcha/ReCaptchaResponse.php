<?php

namespace LeTraceurSnork\UnofficialCaptchaSdk\ReCaptcha;

use DateTime;
use DateTimeInterface;
use Exception;
use LeTraceurSnork\Captcha\CaptchaException;
use LeTraceurSnork\Captcha\CaptchaResponseInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Captcha-compatible response for Google reCAPTCHA service.
 */
class ReCaptchaResponse implements CaptchaResponseInterface
{
    /**
     * @var bool
     * @readonly
     */
    protected $isSuccess;

    /**
     * @var array
     */
    protected $errorCodes = [];

    /**
     * @var string|null
     */
    protected $hostname;

    /**
     * @var DateTimeInterface|null
     */
    protected $challengeTs;

    /**
     * @param bool $isSuccess
     */
    public function __construct($isSuccess)
    {
        $this->isSuccess = $isSuccess;
    }

    /**
     * Создание объекта из PSR-7 Response
     *
     * @param ResponseInterface $response
     *
     * @throws CaptchaException
     * @return ReCaptchaResponse
     */
    public static function fromHttpResponse(ResponseInterface $response)
    {
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);

        if ($data === null) {
            throw new CaptchaException('reCaptcha response could not be parsed as JSON.');
        }

        if (!isset($data['success'])) {
            throw new CaptchaException('Malformed reCaptcha response: no "success" field.');
        }

        $isSuccess       = (bool)$data['success'];
        $captchaResponse = new self($isSuccess);

        $challengeTs = isset($data['challenge_ts'])
            ? (string)$data['challenge_ts']
            : null;
        if (isset($challengeTs)) {
            try {
                $challengeTimestamp = new DateTime($challengeTs);
                $captchaResponse->setChallengeTs($challengeTimestamp);
            } catch (Exception $e) {
            }
        }

        $hostname = isset($data['hostname'])
            ? (string)$data['hostname']
            : null;
        if (isset($hostname)) {
            $captchaResponse->setHostname($hostname);
        }

        $errorCodes = isset($data['error-codes'])
            ? (array)$data['error-codes']
            : null;
        if (isset($errorCodes)) {
            $captchaResponse->setErrorCodes($errorCodes);
        }

        return $captchaResponse;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->isSuccess;
    }

    /**
     * @return string[]
     */
    public function getErrorCodes()
    {
        return $this->errorCodes;
    }

    /**
     * @return string|null
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getChallengeTs()
    {
        return $this->challengeTs;
    }

    /**
     * @param string[] $errorCodes
     *
     * @return $this
     */
    protected function setErrorCodes($errorCodes)
    {
        $this->errorCodes = $errorCodes;

        return $this;
    }

    /**
     * @param string|null $hostname
     *
     * @return $this
     */
    protected function setHostname($hostname)
    {
        $this->hostname = $hostname;

        return $this;
    }

    /**
     * @param DateTimeInterface $timestamp
     *
     * @return $this
     */
    protected function setChallengeTs($timestamp)
    {
        $this->challengeTs = $timestamp;

        return $this;
    }
}
