<?php

namespace LeTraceurSnork\UnofficialCaptchaSdk\HCaptcha;

use DateTime;
use DateTimeInterface;
use Exception;
use LeTraceurSnork\Captcha\CaptchaException;
use LeTraceurSnork\Captcha\CaptchaResponseInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Captcha-compatible response for hCaptcha service
 */
class HCaptchaResponse implements CaptchaResponseInterface
{
    /**
     * @var bool
     */
    protected $isSuccess;

    /**
     * @var array
     */
    protected $errorCodes = [];

    /**
     * @var
     */
    protected $hostname;

    /**
     * @var DateTimeInterface
     */
    protected $challengeTimestamp;

    /**
     * @deprecated
     * @var bool
     */
    protected $credit;

    /**
     * @var float
     */
    protected $score;

    /**
     * @var string[]
     */
    protected $score_reason;

    /**
     * @param bool $isSuccess
     */
    public function __construct($isSuccess)
    {
        $this->isSuccess = $isSuccess;
    }

    /**
     * @param ResponseInterface $response
     *
     * @throws CaptchaException
     * @return HCaptchaResponse
     */
    public static function fromHttpResponse(ResponseInterface $response)
    {
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);

        if (is_null($data)) {
            throw new CaptchaException('hCaptcha response could not be parsed as JSON.');
        }
        if (!isset($data['success'])) {
            throw new CaptchaException('Malformed hCaptcha response: no "success" field.');
        }

        $isSuccess = (bool)$data['success'];
        $response  = (new self($isSuccess));

        $challengeTs = isset($data['challenge_ts'])
            ? (string)$data['challenge_ts']
            : null;
        if (isset($challengeTs)) {
            try {
                $challengeTimestamp = new DateTime($challengeTs);
                $response->setChallengeTimestamp($challengeTimestamp);
            } catch (Exception $e) {
            }
        }

        $hostname = isset($data['hostname'])
            ? (string)$data['hostname']
            : null;
        if (isset($hostname)) {
            $response->setHostname($hostname);
        }

        $credit = isset($data['credit'])
            ? (bool)$data['credit']
            : null;
        if (isset($credit)) {
            $response->setCredit($credit);
        }

        $errorCodes = isset($data['error-codes'])
            ? (array)$data['error-codes']
            : null;
        if (isset($errorCodes)) {
            $response->setErrorCodes($errorCodes);
        }

        $score = isset($data['score'])
            ? (float)$data['score']
            : null;
        if (isset($score)) {
            $response->setScore($score);
        }

        $scoreReason = isset($data['score_reason'])
            ? (array)$data['score_reason']
            : null;
        if (isset($scoreReason)) {
            $response->setScoreReason($scoreReason);
        }

        return $response;
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

    public function getHost()
    {
        return $this->hostname;
    }

    public function getChallengeTimestamp()
    {
        return $this->challengeTimestamp;
    }

    /**
     * @deprecated
     * @return bool
     */
    public function getCredit()
    {
        return $this->credit;
    }

    /**
     * @param string[] $errorCodes
     *
     * @return HCaptchaResponse
     */
    protected function setErrorCodes($errorCodes)
    {
        $this->errorCodes = $errorCodes;

        return $this;
    }

    /**
     * @param string|null $hostname
     *
     * @return HCaptchaResponse
     */
    protected function setHostname($hostname)
    {
        $this->hostname = $hostname;

        return $this;
    }

    /**
     * @param DateTimeInterface $challengeTimestamp
     *
     * @return HCaptchaResponse
     */
    protected function setChallengeTimestamp($challengeTimestamp)
    {
        $this->challengeTimestamp = $challengeTimestamp;

        return $this;
    }

    /**
     * @param bool $credit
     *
     * @return HCaptchaResponse
     */
    protected function setCredit($credit)
    {
        $this->credit = $credit;

        return $this;
    }

    /**
     * @param float $score
     *
     * @return $this
     */
    protected function setScore($score)
    {
        $this->score = $score;

        return $this;
    }

    /**
     * @param string[] $scoreReason
     *
     * @return $this
     */
    protected function setScoreReason($scoreReason)
    {
        $this->score_reason = $scoreReason;

        return $this;
    }
}
