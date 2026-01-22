# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## v1.2.0 - 2026-01-22

### Changed

- `SmartCaptcha::SITE_VERIFY_URL` (`https://smartcaptcha.yandexcloud.net/validate` -> `https://smartcaptcha.cloud.yandex.ru/validate`) - Yandex changed SmartCaptcha domain ([@LeTraceurSnork])

### Fixed

- `PHP-CS-Fixer` version pinned to ~3.69.1

## v1.1.1 - 2025-08-08

### Changed

- Service files have been removed from release
- Thrown exceptions of type `RuntimeException` have been specified (narrowed) to `CaptchaException` ([@LeTraceurSnork])

## v1.1.0 - 2025-08-05

### Changed

- `SmartCaptchaResponse` now handles an empty 'host' field: if the 'host' field is empty while 'status': 'ok', such a response is now considered falsy and `isSuccess()` returns false. See why: [point 3](https://yandex.cloud/ru/docs/smartcaptcha/concepts/validation#service-response) ([@LeTraceurSnork])

## v1.0.0 - 2025-07-11

### Added

- Project initialization ([@LeTraceurSnork])
- ReCaptcha verification service presented with its ReCaptchaResponse
- hCaptcha verification service presented with its HCaptchaResponse
- SmartCaptcha verification service presented with its SmartCaptchaResponse

[@LeTraceurSnork]:https://github.com/LeTraceurSnork
