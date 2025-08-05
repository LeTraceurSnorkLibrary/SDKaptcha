<?php

namespace LeTraceurSnork\SDKaptcha\SmartCaptcha;

use LeTraceurSnork\Captcha\CaptchaResponseInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class SmartCaptchaResponse implements CaptchaResponseInterface
{
    /**
     * @var bool Whether CAPTCHA's response is ACTUALLY a success (user recognized as not-a-bot)
     */
    protected $isSuccess;

    /**
     * @var bool boolean interpretation of `status` field from server response
     */
    protected $isStatusOk;

    /**
     * @var string `message` field from server response
     */
    protected $message;

    /**
     * @var string|null `host` field from server response
     */
    protected $host;

    /**
     * @param bool   $success
     * @param string $message
     */
    public function __construct($success, $message = '')
    {
        $this->isStatusOk = $success;
        $this->message    = $message;
    }

    /**
     * @inheritDoc
     *
     * According to SmartCaptcha internal rules, a `status` of "ok" MAY be returned even if the user is a bot —
     * for example, when a `Block cloud` event is triggered (e.g., the SmartCaptcha account is unpaid).
     * In this case, the response will look like {'status': "ok", 'host': ""}.
     * However, this response is misleading and may create a false impression that the captcha has been passed.
     *
     * @link https://yandex.cloud/ru/docs/smartcaptcha/concepts/validation#service-response
     */
    public function isSuccess()
    {
        return $this->isStatusOk() && !empty($this->getHost());
    }

    /**
     * Returns true if 'status' field is "ok", false otherwise
     *
     * @return bool
     */
    public function isStatusOk()
    {
        return $this->isStatusOk;
    }

    /**
     * Returns `message` field of /validate/ route response.
     *
     * @return string|null
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Returns `host` field of /validate/ route response (if present).
     *
     * @return string|null
     */
    public function getHost()
    {
        return isset($this->host)
            ? $this->host
            : null;
    }

    /**
     * Self-factory from PSR-7 Response.
     *
     * @param ResponseInterface $response
     *
     * @throws RuntimeException
     *
     * @return SmartCaptchaResponse
     */
    public static function fromHttpResponse(ResponseInterface $response)
    {
        $body = $response->getBody()->getContents();
        $data = \json_decode($body, true);
        if ($data === null) {
            throw new RuntimeException('SmartCaptcha response could not be parsed as JSON.');
        }
        if (!isset($data['status'])) {
            throw new RuntimeException('Malformed SmartCaptcha response: no "status" field.');
        }

        $isStatusOk       = $data['status'] === 'ok';
        $message          = isset($data['message'])
            ? $data['message']
            : '';
        $captchaResponse = new self($isStatusOk, $message);

        $host = isset($data['host'])
            ? $data['host']
            : null;
        if (isset($host)) {
            $captchaResponse->setHost($host);
        }

        return $captchaResponse;
    }

    /**
     * @param string $host
     *
     * @return $this
     */
    protected function setHost($host)
    {
        $this->host = $host;

        return $this;
    }
}
