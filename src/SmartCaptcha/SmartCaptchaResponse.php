<?php

namespace LeTraceurSnork\UnofficialCaptchaSdk\SmartCaptcha;

use LeTraceurSnork\Captcha\CaptchaResponseInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class SmartCaptchaResponse implements CaptchaResponseInterface
{
    /**
     * @var bool boolean interpretation of `status` field from server response
     */
    protected $isSuccess;

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
        $this->isSuccess = $success;
        $this->message   = $message;
    }

    /**
     * @inheritDoc
     */
    public function isSuccess()
    {
        return $this->isSuccess;
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

        $is_success       = $data['status'] === 'ok';
        $message          = isset($data['message'])
            ? $data['message']
            : '';
        $captcha_response = new self($is_success, $message);

        $host = isset($data['host'])
            ? $data['host']
            : null;
        if (isset($host)) {
            $captcha_response->setHost($host);
        }

        return $captcha_response;
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
