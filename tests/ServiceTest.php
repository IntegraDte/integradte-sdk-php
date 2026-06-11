<?php

declare(strict_types=1);

namespace IntegraDte\Tests;

use BadMethodCallException;
use IntegraDte\Application\Service;
use IntegraDte\Domain\CreateBusinessRequest;
use IntegraDte\Domain\CreateCessionRequest;
use IntegraDte\Domain\CreateDocumentRequest;
use IntegraDte\Domain\CreatePurchaseRequest;
use IntegraDte\Domain\GeneratePdfRequest;
use IntegraDte\Domain\UpdateBusinessRequest;
use IntegraDte\Domain\UploadCertificateRequest;
use IntegraDte\Domain\UploadNumerationRequest;
use IntegraDte\Ports\ExtendedIntegraDteApiInterface;
use IntegraDte\Ports\IntegraDteApiInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ServiceTest extends TestCase
{
    #[DataProvider('legacyOperationProvider')]
    public function testBaseInterfaceAdaptersRemainValidForRepresentativeLegacyOperations(
        string $method,
        array $arguments,
        string $expectedRecordedMethod,
        array $expectedRecordedArguments,
    ): void {
        $api = $this->createLegacyApiSpy();
        $service = new Service($api);

        $result = $service->{$method}(...$arguments);

        self::assertSame(
            [
                'operation' => $expectedRecordedMethod,
                'arguments' => $expectedRecordedArguments,
            ],
            $result
        );
        self::assertSame($expectedRecordedMethod, $api->lastMethod);
        self::assertSame($expectedRecordedArguments, $api->lastArguments);
    }

    public static function legacyOperationProvider(): iterable
    {
        $documentRequest = new CreateDocumentRequest('33', '{"foo":"bar"}', userId: 'user-1');
        $pdfRequest = new GeneratePdfRequest(documentId: 'doc-1');

        yield 'createDocument' => [
            'method' => 'createDocument',
            'arguments' => [$documentRequest],
            'expectedRecordedMethod' => 'createDocument',
            'expectedRecordedArguments' => [$documentRequest],
        ];

        yield 'generatePdf' => [
            'method' => 'generatePdf',
            'arguments' => [$pdfRequest, true],
            'expectedRecordedMethod' => 'generatePdf',
            'expectedRecordedArguments' => [$pdfRequest, true],
        ];

        yield 'getMe' => [
            'method' => 'getMe',
            'arguments' => [],
            'expectedRecordedMethod' => 'getMe',
            'expectedRecordedArguments' => [],
        ];

        yield 'deleteNumeration' => [
            'method' => 'deleteNumeration',
            'arguments' => ['num-1'],
            'expectedRecordedMethod' => 'deleteNumeration',
            'expectedRecordedArguments' => ['num-1'],
        ];

        yield 'getLastUsedFolio' => [
            'method' => 'getLastUsedFolio',
            'arguments' => ['33'],
            'expectedRecordedMethod' => 'getLastUsedFolio',
            'expectedRecordedArguments' => ['33'],
        ];
    }

    #[DataProvider('unsupportedExtendedOperationProvider')]
    public function testExtendedOperationsFailClearlyForBaseInterfaceAdapters(string $method, array $arguments): void
    {
        $service = new Service($this->createBaseApiStub());

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('does not support the requested extended operation');

        $service->{$method}(...$arguments);
    }

    public static function unsupportedExtendedOperationProvider(): iterable
    {
        yield 'getDocuments' => ['method' => 'getDocuments', 'arguments' => [['status' => 'accepted']]];
        yield 'activateLicense' => ['method' => 'activateLicense', 'arguments' => [['license_key' => 'ABC']]];
        yield 'requeueOfflineDocument' => ['method' => 'requeueOfflineDocument', 'arguments' => [['document_id' => 'doc-offline-1']]];
    }

    #[DataProvider('extendedOperationProvider')]
    public function testExtendedOperationsDispatchExactMethodAndArguments(
        string $method,
        array $arguments,
        array $response,
    ): void {
        $api = $this->createExtendedApiSpy();
        $api->responses[$method] = $response;
        $service = new Service($api);

        self::assertSame($response, $service->{$method}(...$arguments));
        self::assertSame($method, $api->lastMethod);
        self::assertSame($arguments, $api->lastArguments);
    }

    public static function extendedOperationProvider(): iterable
    {
        yield 'getDocuments' => [
            'method' => 'getDocuments',
            'arguments' => [['status' => 'accepted', 'page' => 2]],
            'response' => ['items' => [['id' => 'doc-1']]],
        ];

        yield 'getDocumentStatsWithFilters' => [
            'method' => 'getDocumentStatsWithFilters',
            'arguments' => [['from_date' => '2026-01-01', 'to_date' => '2026-01-31']],
            'response' => ['accepted' => 4],
        ];

        yield 'getBusinesses' => [
            'method' => 'getBusinesses',
            'arguments' => [],
            'response' => ['items' => [['id' => 'biz-1']]],
        ];

        yield 'getBusiness' => [
            'method' => 'getBusiness',
            'arguments' => ['biz-1'],
            'response' => ['id' => 'biz-1'],
        ];

        yield 'enableProductionMode' => [
            'method' => 'enableProductionMode',
            'arguments' => [['resolution_number_dte' => '80']],
            'response' => ['ok' => true],
        ];

        yield 'enableCertificationMode' => [
            'method' => 'enableCertificationMode',
            'arguments' => [],
            'response' => ['ok' => true],
        ];

        yield 'getBillingBalance' => [
            'method' => 'getBillingBalance',
            'arguments' => [],
            'response' => ['available' => 5000],
        ];

        yield 'getBillingPayments' => [
            'method' => 'getBillingPayments',
            'arguments' => [['status' => 'COMPLETED', 'page' => 1]],
            'response' => ['data' => [['id' => 'pay-1']]],
        ];

        yield 'getPurchaseAcknowledgments' => [
            'method' => 'getPurchaseAcknowledgments',
            'arguments' => [['tipo_dte' => '33', 'accion_doc' => 'accept']],
            'response' => ['data' => [['id' => 'ack-1']]],
        ];

        yield 'getCurrentCertificate' => [
            'method' => 'getCurrentCertificate',
            'arguments' => [],
            'response' => ['serial' => 'SER-1'],
        ];

        yield 'createLicense' => [
            'method' => 'createLicense',
            'arguments' => [['name' => 'Caja 01']],
            'response' => ['id' => 'lic-1'],
        ];

        yield 'getLicenses' => [
            'method' => 'getLicenses',
            'arguments' => [],
            'response' => ['items' => [['id' => 'lic-1']]],
        ];

        yield 'getLicense' => [
            'method' => 'getLicense',
            'arguments' => ['lic-1'],
            'response' => ['id' => 'lic-1'],
        ];

        yield 'getLicenseDevices' => [
            'method' => 'getLicenseDevices',
            'arguments' => ['lic-1'],
            'response' => ['devices' => [['id' => 'dev-1']]],
        ];

        yield 'enableLicense' => [
            'method' => 'enableLicense',
            'arguments' => ['lic-1', ['reason' => 'manual_enable']],
            'response' => ['enabled' => true],
        ];

        yield 'disableLicense' => [
            'method' => 'disableLicense',
            'arguments' => ['lic-1', ['reason' => 'payment_pending']],
            'response' => ['enabled' => false],
        ];

        yield 'revokeLicense' => [
            'method' => 'revokeLicense',
            'arguments' => ['lic-1', ['reason' => 'device_compromised']],
            'response' => ['revoked' => true],
        ];

        yield 'activateLicense' => [
            'method' => 'activateLicense',
            'arguments' => [['license_key' => 'ABC', 'device_id' => 'machine-id']],
            'response' => ['offline_token' => 'offline-1', 'activated' => true],
        ];

        yield 'refreshLicense' => [
            'method' => 'refreshLicense',
            'arguments' => [['device_id' => 'machine-id', 'offline_token' => 'offline-1']],
            'response' => ['offline_token' => 'offline-2'],
        ];

        yield 'requestNumbers' => [
            'method' => 'requestNumbers',
            'arguments' => [['document_type' => 33, 'quantity' => 4]],
            'response' => [['document_type' => 33, 'start' => 100, 'end' => 103]],
        ];

        yield 'requestNumerationsViaRabbitMq' => [
            'method' => 'requestNumerationsViaRabbitMq',
            'arguments' => [['code_sii' => '33', 'quantity' => 120]],
            'response' => ['queued' => true],
        ];

        yield 'syncDocument' => [
            'method' => 'syncDocument',
            'arguments' => [['document_id' => 'DTE_33_xxx']],
            'response' => ['document_id' => 'DTE_33_xxx', 'status' => 'SYNCED'],
        ];

        yield 'requeueDocument' => [
            'method' => 'requeueDocument',
            'arguments' => [['document_id' => 'doc-1']],
            'response' => ['queued' => true],
        ];

        yield 'requeueOfflineDocument' => [
            'method' => 'requeueOfflineDocument',
            'arguments' => [['document_id' => 'doc-offline-1']],
            'response' => ['queued' => true, 'offline' => true],
        ];

        yield 'requeueDocumentStatus' => [
            'method' => 'requeueDocumentStatus',
            'arguments' => [['document_id' => 'doc-offline-1']],
            'response' => ['queued' => true, 'status_only' => true],
        ];
    }

    private function createBaseApiStub(): IntegraDteApiInterface
    {
        return new class () implements IntegraDteApiInterface {
            public function createDocument(CreateDocumentRequest $request): array
            {
                return [];
            }

            public function getDocument(string $id): array
            {
                return [];
            }

            public function getDocumentStats(): array
            {
                return [];
            }

            public function createCession(CreateCessionRequest $request): array
            {
                return [];
            }

            public function generatePdf(GeneratePdfRequest $request, bool $cedible): array
            {
                return [];
            }

            public function createBusiness(CreateBusinessRequest $request): array
            {
                return [];
            }

            public function updateBusiness(string $id, UpdateBusinessRequest $request): array
            {
                return [];
            }

            public function uploadCertificate(string $businessId, UploadCertificateRequest $request): array
            {
                return [];
            }

            public function getCertificateInfo(): array
            {
                return [];
            }

            public function getMe(): array
            {
                return [];
            }

            public function createPurchase(CreatePurchaseRequest $request): array
            {
                return [];
            }

            public function getNumerationSummary(): array
            {
                return [];
            }

            public function getLastUsedFolio(string $codeSii): array
            {
                return [];
            }

            public function uploadNumeration(UploadNumerationRequest $request): array
            {
                return [];
            }

            public function deleteNumeration(string $id): array
            {
                return [];
            }
        };
    }

    private function createLegacyApiSpy(): object
    {
        return new class () implements IntegraDteApiInterface {
            public string $lastMethod = '';
            /** @var list<mixed> */
            public array $lastArguments = [];

            public function createDocument(CreateDocumentRequest $request): array
            {
                return $this->record(__FUNCTION__, [$request]);
            }

            public function getDocument(string $id): array
            {
                return [];
            }

            public function getDocumentStats(): array
            {
                return [];
            }

            public function createCession(CreateCessionRequest $request): array
            {
                return [];
            }

            public function generatePdf(GeneratePdfRequest $request, bool $cedible): array
            {
                return $this->record(__FUNCTION__, [$request, $cedible]);
            }

            public function createBusiness(CreateBusinessRequest $request): array
            {
                return [];
            }

            public function updateBusiness(string $id, UpdateBusinessRequest $request): array
            {
                return [];
            }

            public function uploadCertificate(string $businessId, UploadCertificateRequest $request): array
            {
                return [];
            }

            public function getCertificateInfo(): array
            {
                return [];
            }

            public function getMe(): array
            {
                return $this->record(__FUNCTION__, []);
            }

            public function createPurchase(CreatePurchaseRequest $request): array
            {
                return [];
            }

            public function getNumerationSummary(): array
            {
                return [];
            }

            public function getLastUsedFolio(string $codeSii): array
            {
                return $this->record(__FUNCTION__, [$codeSii]);
            }

            public function uploadNumeration(UploadNumerationRequest $request): array
            {
                return [];
            }

            public function deleteNumeration(string $id): array
            {
                return $this->record(__FUNCTION__, [$id]);
            }

            /** @param list<mixed> $arguments */
            private function record(string $method, array $arguments): array
            {
                $this->lastMethod = $method;
                $this->lastArguments = $arguments;

                return [
                    'operation' => $method,
                    'arguments' => $arguments,
                ];
            }
        };
    }

    private function createExtendedApiSpy(): object
    {
        return new class () implements ExtendedIntegraDteApiInterface {
            public string $lastMethod = '';
            /** @var list<mixed> */
            public array $lastArguments = [];
            /** @var array<string, array<mixed>> */
            public array $responses = [];

            public function createDocument(CreateDocumentRequest $request): array
            {
                return [];
            }

            public function getDocument(string $id): array
            {
                return [];
            }

            public function getDocuments(array $filters = []): array
            {
                return $this->record(__FUNCTION__, [$filters]);
            }

            public function getDocumentStats(): array
            {
                return [];
            }

            public function getDocumentStatsWithFilters(array $filters = []): array
            {
                return $this->record(__FUNCTION__, [$filters]);
            }

            public function createCession(CreateCessionRequest $request): array
            {
                return [];
            }

            public function generatePdf(GeneratePdfRequest $request, bool $cedible): array
            {
                return [];
            }

            public function createBusiness(CreateBusinessRequest $request): array
            {
                return [];
            }

            public function getBusinesses(): array
            {
                return $this->record(__FUNCTION__, []);
            }

            public function getBusiness(string $id): array
            {
                return $this->record(__FUNCTION__, [$id]);
            }

            public function enableProductionMode(array $payload): array
            {
                return $this->record(__FUNCTION__, [$payload]);
            }

            public function enableCertificationMode(): array
            {
                return $this->record(__FUNCTION__, []);
            }

            public function updateBusiness(string $id, UpdateBusinessRequest $request): array
            {
                return [];
            }

            public function uploadCertificate(string $businessId, UploadCertificateRequest $request): array
            {
                return [];
            }

            public function getCertificateInfo(): array
            {
                return [];
            }

            public function getMe(): array
            {
                return [];
            }

            public function getBillingBalance(): array
            {
                return $this->record(__FUNCTION__, []);
            }

            public function getBillingPayments(array $filters = []): array
            {
                return $this->record(__FUNCTION__, [$filters]);
            }

            public function createPurchase(CreatePurchaseRequest $request): array
            {
                return [];
            }

            public function getPurchaseAcknowledgments(array $filters = []): array
            {
                return $this->record(__FUNCTION__, [$filters]);
            }

            public function getNumerationSummary(): array
            {
                return [];
            }

            public function getLastUsedFolio(string $codeSii): array
            {
                return [];
            }

            public function uploadNumeration(UploadNumerationRequest $request): array
            {
                return [];
            }

            public function deleteNumeration(string $id): array
            {
                return [];
            }

            public function getCurrentCertificate(): array
            {
                return $this->record(__FUNCTION__, []);
            }

            public function createLicense(array $payload): array
            {
                return $this->record(__FUNCTION__, [$payload]);
            }

            public function getLicenses(): array
            {
                return $this->record(__FUNCTION__, []);
            }

            public function getLicense(string $id): array
            {
                return $this->record(__FUNCTION__, [$id]);
            }

            public function getLicenseDevices(string $id): array
            {
                return $this->record(__FUNCTION__, [$id]);
            }

            public function enableLicense(string $id, array $payload = []): array
            {
                return $this->record(__FUNCTION__, [$id, $payload]);
            }

            public function disableLicense(string $id, array $payload = []): array
            {
                return $this->record(__FUNCTION__, [$id, $payload]);
            }

            public function revokeLicense(string $id, array $payload = []): array
            {
                return $this->record(__FUNCTION__, [$id, $payload]);
            }

            public function activateLicense(array $payload): array
            {
                return $this->record(__FUNCTION__, [$payload]);
            }

            public function refreshLicense(array $payload): array
            {
                return $this->record(__FUNCTION__, [$payload]);
            }

            /**
             * @return list<array<string, mixed>>
             */
            public function requestNumbers(array $payload): array
            {
                return $this->record(__FUNCTION__, [$payload]);
            }

            public function requestNumerationsViaRabbitMq(array $payload): array
            {
                return $this->record(__FUNCTION__, [$payload]);
            }

            public function syncDocument(array $payload): array
            {
                return $this->record(__FUNCTION__, [$payload]);
            }

            public function requeueDocument(array $payload): array
            {
                return $this->record(__FUNCTION__, [$payload]);
            }

            public function requeueOfflineDocument(array $payload): array
            {
                return $this->record(__FUNCTION__, [$payload]);
            }

            public function requeueDocumentStatus(array $payload): array
            {
                return $this->record(__FUNCTION__, [$payload]);
            }

            /** @param list<mixed> $arguments */
            private function record(string $method, array $arguments): array
            {
                $this->lastMethod = $method;
                $this->lastArguments = $arguments;

                return $this->responses[$method] ?? [];
            }
        };
    }
}
