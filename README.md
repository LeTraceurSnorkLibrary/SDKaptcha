# SDKaptcha

Unofficial SDK for different Captcha external providers

## Installation

```bash
composer require letraceursnork/sdkaptcha
```

## Usage example

As described [in this proposed CaptchaInterface](https://github.com/LeTraceurSnork/letraceursnork-captcha-verifier), all of provided Captcha verifiers share common interface and their basic simple usage is:

```php
// first we get User's token `token` from, say, front-end
$token = 'User token from front-end';

$secret_recaptcha_key = 'recaptcha-secret-token';
$recaptcha = new \LeTraceurSnork\SDKaptcha\ReCaptcha\ReCaptcha($secret_recaptcha_key);
$recaptcha_response = $recaptcha->verify($token);
if ($recaptcha_response->isSuccess()) {
    // ... ReCaptcha successfully passed
}

$secret_hcaptcha_key = 'hcaptcha-secret-token';
$hcaptcha = new \LeTraceurSnork\SDKaptcha\HCaptcha\HCaptcha($secret_hcaptcha_key);
$hcaptcha_response = $hcaptcha->verify($token);
if ($hcaptcha_response->isSuccess()) {
    // ... hCaptcha successfully passed
}

$secret_smartcaptcha_key = 'smart-captcha-secret-token';
$smart_captcha = new \LeTraceurSnork\SDKaptcha\SmartCaptcha\SmartCaptcha($secret_smartcaptcha_key);
$smartcaptcha_response = $smart_captcha->verify($token);
if ($smartcaptcha_response->isSuccess()) {
    // ... Smart Captcha successfully passed
}

// ... you got the idea
// Please see basic interface for details
// ... or see specific Captcha verifier for specific details, especially their inner fields, like getScore() for ReCaptcha
```
