# Changelog

## [0.3.1](https://github.com/IntegraDte/integradte-sdk-php/compare/integradte-sdk-php-v0.3.0...integradte-sdk-php-v0.3.1) (2026-09-12)


### Features

* **api:** cover the full public API and always send idempotency-key ([c04831d](https://github.com/IntegraDte/integradte-sdk-php/commit/c04831ddb72de642cbf0b4775bf7775a3ab36dab))
* **api:** cover the full public API and always send idempotency-key ([55226cd](https://github.com/IntegraDte/integradte-sdk-php/commit/55226cdc5ee4ecebfb2e5b2cbe13206ae6d22b12))

## [0.3.0](https://github.com/IntegraDte/integradte-sdk-php/compare/integradte-sdk-php-v0.2.1...integradte-sdk-php-v0.3.0) (2026-09-12)


### ⚠ BREAKING CHANGES

* **api:** removed Client/Service/ExtendedIntegraDteApiInterface methods requeueOfflineDocument() and requestNumerationsViaRabbitMq(). Use requeueDocument() to requeue documents and requestNumbers() to request folios.

### Features

* **api:** drop offline document requeue and RabbitMQ numeration request ([13035a6](https://github.com/IntegraDte/integradte-sdk-php/commit/13035a65d3f53b9068cc2d4a4ac5b230e6283b27))

## [0.2.1](https://github.com/IntegraDte/integradte-sdk-php/compare/integradte-sdk-php-v0.2.0...integradte-sdk-php-v0.2.1) (2026-09-11)


### Bug Fixes

* **api:** point createPurchase and requestNumbers at the current API routes ([aaaed9e](https://github.com/IntegraDte/integradte-sdk-php/commit/aaaed9ef5e25d549a574f3542fd59f8de2386fac))
* **api:** point createPurchase and requestNumbers at the current API routes ([092f58d](https://github.com/IntegraDte/integradte-sdk-php/commit/092f58d63ea6071dd50d39ffb7e66119f94c92ab))

## [0.2.0](https://github.com/IntegraDte/integradte-sdk-php/compare/integradte-sdk-php-v0.1.4...integradte-sdk-php-v0.2.0) (2026-09-11)


### ⚠ BREAKING CHANGES

* **api:** removed Client/Service/ExtendedIntegraDteApiInterface methods getCurrentCertificate(), createLicense(), getLicenses(), getLicense(), getLicenseDevices(), enableLicense(), disableLicense(), revokeLicense(), activateLicense(), refreshLicense() and syncDocument(). getCertificateInfo() now returns {"success", "message", "data": {"has_valid_certificate": bool}} instead of the certificate details (subject, RUT, dates), and a business without a certificate is a 200 with false instead of a 400 ApiError.

### Features

* **api:** sync SDK with the public API, drop licenses, document sync and current certificate ([ed4ff18](https://github.com/IntegraDte/integradte-sdk-php/commit/ed4ff180d98c4bd4b253f8a2252db493583b1cda))
* **ports:** create ExtendedIntegraDteApiInterface for extended API functionalities ([4a7adc7](https://github.com/IntegraDte/integradte-sdk-php/commit/4a7adc755ab20a9b3bd4110086e70eeeedd7ab62))
* **service:** add extended API methods for document and license management ([4a7adc7](https://github.com/IntegraDte/integradte-sdk-php/commit/4a7adc755ab20a9b3bd4110086e70eeeedd7ab62))
* **tests:** implement comprehensive tests for new service methods and API interactions ([4a7adc7](https://github.com/IntegraDte/integradte-sdk-php/commit/4a7adc755ab20a9b3bd4110086e70eeeedd7ab62))

## [0.1.4](https://github.com/IntegraDte/integradte-sdk-php/compare/integradte-sdk-php-v0.1.3...integradte-sdk-php-v0.1.4) (2026-03-18)


### Bug Fixes

* **package:** update package name and homepage to reflect correct organization ([bfd8f07](https://github.com/IntegraDte/integradte-sdk-php/commit/bfd8f073fb3a77edb6e27b8c7b67d4b22a563c6e))

## [0.1.3](https://github.com/IntegraDte/integradte-sdk-php/compare/integradte-sdk-php-v0.1.2...integradte-sdk-php-v0.1.3) (2026-03-18)

### Features

- **api:** create requests for PDF generation, certificate upload, and numeration upload ([7614ed7](https://github.com/IntegraDte/integradte-sdk-php/commit/7614ed7ea934858fd1f2b197661a375fad8a4a98))
- **api:** define IntegraFacturacionApiInterface for API interactions ([7614ed7](https://github.com/IntegraDte/integradte-sdk-php/commit/7614ed7ea934858fd1f2b197661a375fad8a4a98))
- **dte:** add JsonHelpers trait for JSON serialization ([7614ed7](https://github.com/IntegraDte/integradte-sdk-php/commit/7614ed7ea934858fd1f2b197661a375fad8a4a98))
- **dte:** implement DTE document structures and builder for various types ([7614ed7](https://github.com/IntegraDte/integradte-sdk-php/commit/7614ed7ea934858fd1f2b197661a375fad8a4a98))

### Bug Fixes

- **composer:** update content-hash to match current dependencies ([0599e55](https://github.com/IntegraDte/integradte-sdk-php/commit/0599e553d1864fe87ed7608d8e9a725104905341))
- **readme:** correct URL in SDK description ([5127c5e](https://github.com/IntegraDte/integradte-sdk-php/commit/5127c5e9130bd2bb543422bc795ea78fb36e0fd2))

## [0.1.2](https://github.com/JoseLuis21/integrafacturacion-sdk-php/compare/integradte-sdk-php-v0.1.1...integradte-sdk-php-v0.1.2) (2026-03-11)

### Features

- **api:** create requests for PDF generation, certificate upload, and numeration upload ([7614ed7](https://github.com/IntegraDte/integrafacturacion-sdk-php/commit/7614ed7ea934858fd1f2b197661a375fad8a4a98))
- **api:** define IntegraFacturacionApiInterface for API interactions ([7614ed7](https://github.com/IntegraDte/integrafacturacion-sdk-php/commit/7614ed7ea934858fd1f2b197661a375fad8a4a98))
- **dte:** add JsonHelpers trait for JSON serialization ([7614ed7](https://github.com/IntegraDte/integrafacturacion-sdk-php/commit/7614ed7ea934858fd1f2b197661a375fad8a4a98))
- **dte:** implement DTE document structures and builder for various types ([7614ed7](https://github.com/IntegraDte/integrafacturacion-sdk-php/commit/7614ed7ea934858fd1f2b197661a375fad8a4a98))

### Bug Fixes

- **readme:** correct URL in SDK description ([5127c5e](https://github.com/IntegraDte/integrafacturacion-sdk-php/commit/5127c5e9130bd2bb543422bc795ea78fb36e0fd2))

## [0.1.1](https://github.com/IntegraDte/integrafacturacion-sdk-php/compare/integrafacturacion-sdk-php-v0.1.0...integrafacturacion-sdk-php-v0.1.1) (2026-02-27)

### Features

- **api:** create requests for PDF generation, certificate upload, and numeration upload ([7614ed7](https://github.com/IntegraDte/integrafacturacion-sdk-php/commit/7614ed7ea934858fd1f2b197661a375fad8a4a98))
- **api:** define IntegraFacturacionApiInterface for API interactions ([7614ed7](https://github.com/IntegraDte/integrafacturacion-sdk-php/commit/7614ed7ea934858fd1f2b197661a375fad8a4a98))
- **dte:** add JsonHelpers trait for JSON serialization ([7614ed7](https://github.com/IntegraDte/integrafacturacion-sdk-php/commit/7614ed7ea934858fd1f2b197661a375fad8a4a98))
- **dte:** implement DTE document structures and builder for various types ([7614ed7](https://github.com/IntegraDte/integrafacturacion-sdk-php/commit/7614ed7ea934858fd1f2b197661a375fad8a4a98))

### Bug Fixes

- **readme:** correct URL in SDK description ([5127c5e](https://github.com/IntegraDte/integrafacturacion-sdk-php/commit/5127c5e9130bd2bb543422bc795ea78fb36e0fd2))
