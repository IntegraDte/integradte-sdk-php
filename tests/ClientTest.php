<?php

declare(strict_types=1);

namespace IntegraDte\Tests;

use IntegraDte\Adapters\HttpIntegra\ApiError;
use IntegraDte\Adapters\HttpIntegra\Client;
use IntegraDte\Adapters\HttpIntegra\Config;
use IntegraDte\Adapters\HttpIntegra\HttpResponse;
use IntegraDte\Adapters\HttpIntegra\HttpTransportInterface;
use BadMethodCallException;
use Closure;
use IntegraDte\Domain\CreateBusinessRequest;
use IntegraDte\Domain\CreateCessionRequest;
use IntegraDte\Domain\CreateDocumentRequest;
use IntegraDte\Domain\CreateFirstBusinessRequest;
use IntegraDte\Domain\CreatePurchaseRequest;
use IntegraDte\Domain\GeneratePdfRequest;
use IntegraDte\Domain\LoginRequest;
use IntegraDte\Domain\LowStockConfigItem;
use IntegraDte\Domain\RequeueCessionRequest;
use IntegraDte\Domain\RequeuePurchaseRequest;
use IntegraDte\Domain\UpdateBusinessRequest;
use IntegraDte\Domain\UpdateDocumentRequest;
use IntegraDte\Domain\UpdateLowStockConfigRequest;
use IntegraDte\Domain\UpdateNumerationNextNumberRequest;
use IntegraDte\Domain\UploadCertificateRequest;
use IntegraDte\Domain\UploadNumerationRequest;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    public function testCreateDocumentSendsIdempotencyHeader(): void
    {
        $transport = new RecordingTransport();

        $client = new Client(new Config(apiKey: 'key', transport: $transport));
        $client->createDocument(new CreateDocumentRequest('33', '{"foo":"bar"}', idempotencyKey: 'idem-1'));

        self::assertArrayHasKey('idempotency-key', $transport->headers);
        self::assertSame('idem-1', $transport->headers['idempotency-key']);
    }

    public function testApiErrorOnNon2xx(): void
    {
        $transport = new class () implements HttpTransportInterface {
            public function send(string $method, string $url, array $headers, ?string $body): HttpResponse
            {
                return new HttpResponse(422, '{"error":"bad"}');
            }
        };

        $client = new Client(new Config(apiKey: 'key', transport: $transport));

        $this->expectException(ApiError::class);
        $client->getDocumentStats();
    }

    public function testGetLastUsedFolioBuildsQuery(): void
    {
        $transport = new RecordingTransport();

        $client = new Client(new Config(apiKey: 'key', baseUrl: 'https://api.integradte.cl', transport: $transport));
        $client->getLastUsedFolio('33');

        $this->assertRecordedRequest($transport->history[0], 'GET', '/api/v1/numerations/last-used-number', null, [
            'code_sii' => '33',
        ]);
    }

    public function testGetDocumentsBuildsFilterQuery(): void
    {
        $transport = new RecordingTransport();

        $client = new Client(new Config(apiKey: 'key', baseUrl: 'https://api.integradte.cl', transport: $transport));
        $client->getDocuments([
            'code_sii' => '33',
            'status' => 'accepted',
            'page' => 2,
            'limit' => 50,
            'from_date' => '2026-01-01',
            'to_date' => '2026-01-31',
        ]);

        $this->assertRecordedRequest($transport->history[0], 'GET', '/api/v1/documents', null, [
            'code_sii' => '33',
            'status' => 'accepted',
            'page' => '2',
            'limit' => '50',
            'from_date' => '2026-01-01',
            'to_date' => '2026-01-31',
        ]);
    }

    public function testBusinessAndBillingEndpointsUseExpectedRoutes(): void
    {
        $transport = new RecordingTransport();
        $client = new Client(new Config(apiKey: 'key', baseUrl: 'https://api.integradte.cl', transport: $transport));

        $client->getBusinesses();
        $this->assertRecordedRequest($transport->history[0], 'GET', '/api/v1/businesses');

        $client->getBusiness('biz 1');
        $this->assertRecordedRequest($transport->history[1], 'GET', '/api/v1/businesses/biz%201');

        $client->enableProductionMode(['resolution_number_dte' => '80']);
        $this->assertRecordedRequest($transport->history[2], 'POST', '/api/v1/businesses/production-mode', [
            'resolution_number_dte' => '80',
        ]);

        $client->enableCertificationMode();
        $this->assertRecordedRequest($transport->history[3], 'POST', '/api/v1/businesses/certification-mode');

        $client->getBillingBalance();
        $this->assertRecordedRequest($transport->history[4], 'GET', '/api/v1/billing/balance');

        $client->getBillingPayments(['status' => 'COMPLETED', 'page' => 1, 'limit' => 20]);
        $this->assertRecordedRequest($transport->history[5], 'GET', '/api/v1/billing/payments', null, [
            'status' => 'COMPLETED',
            'page' => '1',
            'limit' => '20',
        ]);
    }

    public function testNumerationAndDocumentUtilityEndpointsUseExpectedRoutes(): void
    {
        $transport = new RecordingTransport();
        $client = new Client(new Config(apiKey: 'key', baseUrl: 'https://api.integradte.cl', transport: $transport));

        $client->requestNumbers(['document_type' => 33, 'quantity' => 4]);
        $this->assertRecordedRequest($transport->history[0], 'POST', '/api/v1/numerations/request', [
            'document_type' => 33,
            'quantity' => 4,
        ]);

        $client->requeueDocument(['document_id' => 'doc-1']);
        $this->assertRecordedRequest($transport->history[1], 'POST', '/api/v1/documents/requeue', [
            'document_id' => 'doc-1',
        ]);

        $client->requeueDocumentStatus(['document_id' => 'doc-1']);
        $this->assertRecordedRequest($transport->history[2], 'POST', '/api/v1/documents/requeue/status', [
            'document_id' => 'doc-1',
        ]);
    }

    public function testGetCertificateInfoReturnsValidityFlag(): void
    {
        $transport = new RecordingTransport([
            '{"success":true,"message":"certificate info retrieved successfully","data":{"has_valid_certificate":true}}',
            '{"success":true,"message":"certificate info retrieved successfully","data":{"has_valid_certificate":false}}',
        ]);
        $client = new Client(new Config(apiKey: 'key', baseUrl: 'https://api.integradte.cl', transport: $transport));

        self::assertSame(
            [
                'success' => true,
                'message' => 'certificate info retrieved successfully',
                'data' => ['has_valid_certificate' => true],
            ],
            $client->getCertificateInfo()
        );
        $this->assertRecordedRequest($transport->history[0], 'GET', '/api/v1/business/certificate-info');

        // A business without a certificate is a 200 with false, not an error.
        self::assertFalse($client->getCertificateInfo()['data']['has_valid_certificate']);
        $this->assertRecordedRequest($transport->history[1], 'GET', '/api/v1/business/certificate-info');
    }

    public function testCreatePurchasePostsToPurchaseAcknowledgmentsWithIdempotencyKey(): void
    {
        $transport = new RecordingTransport();
        $client = new Client(new Config(apiKey: 'key', baseUrl: 'https://api.integradte.cl', transport: $transport));

        $client->createPurchase(new CreatePurchaseRequest(
            xmlBase64: 'BASE64',
            rutEmisor: '76123456-7',
            razonSocialEmisor: 'Proveedor SpA',
            tipoDte: '33',
            folio: 1234,
            mntTotal: '119000',
            fechaEmision: '2026-09-01',
            emailEmisor: 'dte@proveedor.cl',
            accionDoc: 'ACD',
            idempotencyKey: 'idem-purchase-1',
        ));

        $this->assertRecordedRequest($transport->history[0], 'POST', '/api/v1/purchase-acknowledgments', [
            'xml_base64' => 'BASE64',
            'rut_emisor' => '76123456-7',
            'razon_social_emisor' => 'Proveedor SpA',
            'tipo_dte' => '33',
            'folio' => 1234,
            'mnt_total' => '119000',
            'fecha_emision' => '2026-09-01',
            'email_emisor' => 'dte@proveedor.cl',
            'accion_doc' => 'ACD',
        ]);
        self::assertSame('idem-purchase-1', $transport->history[0]['headers']['idempotency-key'] ?? null);
    }

    public function testPurchaseAcknowledgmentsAndStatsFiltersUseExpectedRoutes(): void
    {
        $transport = new RecordingTransport();
        $client = new Client(new Config(apiKey: 'key', baseUrl: 'https://api.integradte.cl', transport: $transport));

        $client->getPurchaseAcknowledgments(['tipo_dte' => '33', 'accion_doc' => 'accept', 'page' => 1]);
        $this->assertRecordedRequest($transport->history[0], 'GET', '/api/v1/purchase-acknowledgments', null, [
            'tipo_dte' => '33',
            'accion_doc' => 'accept',
            'page' => '1',
        ]);

        $client->getDocumentStatsWithFilters(['from_date' => '2026-01-01', 'to_date' => '2026-02-23']);
        $this->assertRecordedRequest($transport->history[1], 'GET', '/api/v1/documents/stats', null, [
            'from_date' => '2026-01-01',
            'to_date' => '2026-02-23',
        ]);
    }

    public function testSpecialEndpointsPreserveNonStandardResponseShapes(): void
    {
        $transport = new RecordingTransport([
            '[{"document_type":33,"start":100,"end":103}]',
        ]);
        $client = new Client(new Config(apiKey: 'key', baseUrl: 'https://api.integradte.cl', transport: $transport));

        $requestNumbersPayload = ['document_type' => 33, 'quantity' => 4];
        self::assertSame(
            [['document_type' => 33, 'start' => 100, 'end' => 103]],
            $client->requestNumbers($requestNumbersPayload)
        );
        self::assertSame($requestNumbersPayload, json_decode((string) $transport->history[0]['body'], true, 512, JSON_THROW_ON_ERROR));
    }

    public function testHealthAndLoginSendNoCredentials(): void
    {
        $transport = new RecordingTransport([
            '{"service":"integradte-api-client","started_at":"2026-09-12T10:00:00Z","uptime_seconds":12}',
        ]);
        $client = new Client(new Config(apiKey: 'key', baseUrl: 'https://api.integradte.cl', transport: $transport));

        // /health answers raw JSON, without the success/data envelope.
        self::assertSame(
            ['service' => 'integradte-api-client', 'started_at' => '2026-09-12T10:00:00Z', 'uptime_seconds' => 12],
            $client->getHealth()
        );
        $this->assertRecordedRequest($transport->history[0], 'GET', '/api/v1/health');

        $client->login(new LoginRequest(email: 'a@b.cl', password: 'secret'));
        $this->assertRecordedRequest($transport->history[1], 'POST', '/api/v1/auth/login', [
            'email' => 'a@b.cl',
            'password' => 'secret',
        ]);

        foreach ($transport->history as $record) {
            self::assertArrayNotHasKey('x-api-key', $record['headers']);
            self::assertArrayNotHasKey('idempotency-key', $record['headers']);
        }
    }

    public function testCreateFirstBusinessSendsUserKeyInsteadOfApiKey(): void
    {
        $transport = new RecordingTransport();
        $client = new Client(new Config(apiKey: 'key', baseUrl: 'https://api.integradte.cl', transport: $transport));

        $client->createFirstBusiness(self::firstBusinessRequest(), 'user-key-1');

        $record = $transport->history[0];
        $this->assertRecordedRequest($record, 'POST', '/api/v1/onboarding/businesses', [
            'businessName' => 'Empresa SpA',
            'rut' => '76000000-0',
            'activity' => 'Software',
            'address' => 'Av. Apoquindo 3000',
            'commune' => 'Las Condes',
            'region' => 'Metropolitana',
            'emailDte' => 'dte@empresa.cl',
            'emailContact' => 'contacto@empresa.cl',
            'rutLegalAgent' => '12345678-9',
            'fullNameLegalAgent' => 'Ana Perez',
            'resolutionNumberDte' => '0',
            'resolutionDateDte' => '2014-08-22',
            'resolutionNumberTicket' => '0',
            'resolutionTicketDate' => '2014-08-22',
        ]);
        self::assertSame('user-key-1', $record['headers']['x-user-key'] ?? null);
        self::assertArrayNotHasKey('x-api-key', $record['headers']);
        self::assertArrayNotHasKey('idempotency-key', $record['headers']);
    }

    public function testCreateFirstBusinessRequiresUserKey(): void
    {
        $transport = new RecordingTransport();
        $client = new Client(new Config(apiKey: 'key', transport: $transport));

        try {
            $client->createFirstBusiness(self::firstBusinessRequest(), '  ');
            self::fail('expected InvalidArgumentException');
        } catch (InvalidArgumentException $e) {
            self::assertStringContainsString('user key is required', $e->getMessage());
        }

        self::assertSame([], $transport->history);
    }

    public function testUpdateDocumentPutsDteToEncodedId(): void
    {
        $transport = new RecordingTransport();
        $client = new Client(new Config(apiKey: 'key', baseUrl: 'https://api.integradte.cl', transport: $transport));

        $client->updateDocument('doc 1', new UpdateDocumentRequest(
            dataDteJson: ['Encabezado' => ['IdDoc' => ['TipoDTE' => 33]]],
            idempotencyKey: '0190f5b4-7c1e-7a3b-9c2d-1e2f3a4b5c6d',
        ));
        $this->assertRecordedRequest($transport->history[0], 'PUT', '/api/v1/documents/doc%201', [
            'data_dte_json' => ['Encabezado' => ['IdDoc' => ['TipoDTE' => 33]]],
        ]);
        self::assertSame('0190f5b4-7c1e-7a3b-9c2d-1e2f3a4b5c6d', $transport->history[0]['headers']['idempotency-key'] ?? null);

        $client->updateDocument('doc-2', new UpdateDocumentRequest(dataDte: '{"Encabezado":{}}'));
        $this->assertRecordedRequest($transport->history[1], 'PUT', '/api/v1/documents/doc-2', [
            'data_dte' => '{"Encabezado":{}}',
        ]);
    }

    public function testNumerationEndpointsUseExpectedRoutes(): void
    {
        $transport = new RecordingTransport();
        $client = new Client(new Config(apiKey: 'key', baseUrl: 'https://api.integradte.cl', transport: $transport));

        $client->updateNumerationNextNumber('range/1', new UpdateNumerationNextNumberRequest(nextNumber: 150));
        $this->assertRecordedRequest($transport->history[0], 'PATCH', '/api/v1/numerations/range%2F1/next-number', [
            'next_number' => 150,
        ]);

        $client->updateLowStockConfig(new UpdateLowStockConfigRequest([
            new LowStockConfigItem(codeSii: '33', threshold: 20, requestQuantity: 100),
            new LowStockConfigItem(codeSii: '39', threshold: 0, requestQuantity: 500),
        ]));
        $this->assertRecordedRequest($transport->history[1], 'PATCH', '/api/v1/numerations/low-stock', [
            'items' => [
                ['code_sii' => '33', 'threshold' => 20, 'request_quantity' => 100],
                ['code_sii' => '39', 'threshold' => 0, 'request_quantity' => 500],
            ],
        ]);

        $client->listNumerationRanges(['code_sii' => '33']);
        $this->assertRecordedRequest($transport->history[2], 'GET', '/api/v1/numerations/ranges', null, [
            'code_sii' => '33',
        ]);

        $client->listNumerationRanges();
        $this->assertRecordedRequest($transport->history[3], 'GET', '/api/v1/numerations/ranges');
    }

    public function testRequeueEndpointsPostIdsAndOnlyForwardCallerIdempotencyKey(): void
    {
        $transport = new RecordingTransport();
        $client = new Client(new Config(apiKey: 'key', baseUrl: 'https://api.integradte.cl', transport: $transport));

        $client->requeuePurchase(new RequeuePurchaseRequest('pur-1'));
        $this->assertRecordedRequest($transport->history[0], 'POST', '/api/v1/purchase-acknowledgments/requeue', [
            'purchase_id' => 'pur-1',
        ]);
        self::assertArrayNotHasKey('idempotency-key', $transport->history[0]['headers']);

        $client->requeueCession(new RequeueCessionRequest('ces-1', idempotencyKey: 'label-1'));
        $this->assertRecordedRequest($transport->history[1], 'POST', '/api/v1/cessions/requeue', [
            'cession_id' => 'ces-1',
        ]);
        self::assertSame('label-1', $transport->history[1]['headers']['idempotency-key'] ?? null);
    }

    public function testBillingAndConsumptionEndpointsUseExpectedRoutes(): void
    {
        $transport = new RecordingTransport();
        $client = new Client(new Config(apiKey: 'key', baseUrl: 'https://api.integradte.cl', transport: $transport));

        $client->listBillingCharges([
            'page' => 2,
            'limit' => 50,
            'status' => 'charged',
            'pricing_key' => 'emission',
            'from_date' => '2026-09-01',
            'to_date' => '2026-09-30',
        ]);
        $this->assertRecordedRequest($transport->history[0], 'GET', '/api/v1/billing/charges', null, [
            'page' => '2',
            'limit' => '50',
            'status' => 'charged',
            'pricing_key' => 'emission',
            'from_date' => '2026-09-01',
            'to_date' => '2026-09-30',
        ]);

        $client->listBillingPlans();
        $this->assertRecordedRequest($transport->history[1], 'GET', '/api/v1/billing/plans');

        $client->listBillingInvoices(['status' => 'open']);
        $this->assertRecordedRequest($transport->history[2], 'GET', '/api/v1/billing/invoices', null, ['status' => 'open']);

        $client->previewSubscriptionUpgrade('pro plan');
        $this->assertRecordedRequest($transport->history[3], 'GET', '/api/v1/billing/subscription/upgrade/preview', null, [
            'plan_id' => 'pro plan',
        ]);

        $client->getConsumption();
        $this->assertRecordedRequest($transport->history[4], 'GET', '/api/v1/consumption');

        $client->listConsumptionOverages(['page' => 1, 'limit' => 20]);
        $this->assertRecordedRequest($transport->history[5], 'GET', '/api/v1/consumption/overages', null, [
            'page' => '1',
            'limit' => '20',
        ]);

        $client->listConsumptionOperations(['period' => '2026-09']);
        $this->assertRecordedRequest($transport->history[6], 'GET', '/api/v1/consumption/operations', null, [
            'period' => '2026-09',
        ]);

        foreach ($transport->history as $record) {
            self::assertSame('key', $record['headers']['x-api-key'] ?? null);
            self::assertArrayNotHasKey('idempotency-key', $record['headers']);
        }
    }

    public function testCessionReadEndpointsUseExpectedRoutes(): void
    {
        $transport = new RecordingTransport();
        $client = new Client(new Config(apiKey: 'key', baseUrl: 'https://api.integradte.cl', transport: $transport));

        $client->listCessions(['document_id' => 'doc-1', 'page' => 1, 'limit' => 20]);
        $this->assertRecordedRequest($transport->history[0], 'GET', '/api/v1/cessions', null, [
            'document_id' => 'doc-1',
            'page' => '1',
            'limit' => '20',
        ]);

        $client->getCession('ces 1');
        $this->assertRecordedRequest($transport->history[1], 'GET', '/api/v1/cessions/ces%201');
    }

    #[DataProvider('idempotentRouteProvider')]
    public function testIdempotentRoutesSendNewUuidV4WhenCallerGivesNone(Closure $call, string $method, string $path): void
    {
        $transport = new RecordingTransport();
        $client = new Client(new Config(apiKey: 'key', baseUrl: 'https://api.integradte.cl', transport: $transport));

        $call($client, null);
        $call($client, null);

        self::assertSame($method, $transport->history[0]['method']);
        self::assertSame($path, parse_url($transport->history[0]['url'], PHP_URL_PATH));

        $first = $transport->history[0]['headers']['idempotency-key'] ?? '';
        $second = $transport->history[1]['headers']['idempotency-key'] ?? '';
        self::assertMatchesRegularExpression(self::UUID_V4, $first);
        self::assertMatchesRegularExpression(self::UUID_V4, $second);
        self::assertNotSame($first, $second, 'each call must get its own key');
    }

    #[DataProvider('idempotentRouteProvider')]
    public function testIdempotentRoutesHonorCallerKey(Closure $call, string $method, string $path): void
    {
        $transport = new RecordingTransport();
        $client = new Client(new Config(apiKey: 'key', baseUrl: 'https://api.integradte.cl', transport: $transport));

        $call($client, 'caller-key-1');

        self::assertSame($method, $transport->history[0]['method']);
        self::assertSame($path, parse_url($transport->history[0]['url'], PHP_URL_PATH));
        self::assertSame('caller-key-1', $transport->history[0]['headers']['idempotency-key'] ?? null);
    }

    /**
     * Routes that mount IdempotencyMiddleware in the API (internal/routes/private.routes.go).
     *
     * @return iterable<string, array{0: Closure(Client, ?string): mixed, 1: string, 2: string}>
     */
    public static function idempotentRouteProvider(): iterable
    {
        yield 'createDocument' => [
            static fn (Client $c, ?string $key) => $c->createDocument(new CreateDocumentRequest('33', '{}', idempotencyKey: $key)),
            'POST',
            '/api/v1/documents',
        ];
        yield 'updateDocument' => [
            static fn (Client $c, ?string $key) => $c->updateDocument('doc-1', new UpdateDocumentRequest(dataDte: '{}', idempotencyKey: $key)),
            'PUT',
            '/api/v1/documents/doc-1',
        ];
        yield 'createBusiness' => [
            static fn (Client $c, ?string $key) => $c->createBusiness(self::businessRequest(CreateBusinessRequest::class, $key)),
            'POST',
            '/api/v1/businesses',
        ];
        yield 'updateBusiness' => [
            static fn (Client $c, ?string $key) => $c->updateBusiness('biz-1', self::businessRequest(UpdateBusinessRequest::class, $key)),
            'PUT',
            '/api/v1/businesses/biz-1',
        ];
        yield 'uploadCertificate' => [
            static fn (Client $c, ?string $key) => $c->uploadCertificate(
                'biz-1',
                new UploadCertificateRequest('BASE64', 'secret', '2027-01-01', idempotencyKey: $key)
            ),
            'PUT',
            '/api/v1/business/biz-1/certificate',
        ];
        yield 'uploadNumeration' => [
            static fn (Client $c, ?string $key) => $c->uploadNumeration(
                new UploadNumerationRequest('33', 1, 100, 'CAF', '2026-09-01', '2027-03-01', idempotencyKey: $key)
            ),
            'PUT',
            '/api/v1/numerations',
        ];
        yield 'deleteNumeration' => [
            static fn (Client $c, ?string $key) => $c->deleteNumeration('num-1', $key),
            'DELETE',
            '/api/v1/numerations/num-1',
        ];
        yield 'updateNumerationNextNumber' => [
            static fn (Client $c, ?string $key) => $c->updateNumerationNextNumber(
                'range-1',
                new UpdateNumerationNextNumberRequest(10, idempotencyKey: $key)
            ),
            'PATCH',
            '/api/v1/numerations/range-1/next-number',
        ];
        yield 'updateLowStockConfig' => [
            static fn (Client $c, ?string $key) => $c->updateLowStockConfig(
                new UpdateLowStockConfigRequest([new LowStockConfigItem('33', 5, 100)], idempotencyKey: $key)
            ),
            'PATCH',
            '/api/v1/numerations/low-stock',
        ];
        yield 'createPurchase' => [
            static fn (Client $c, ?string $key) => $c->createPurchase(new CreatePurchaseRequest(
                'BASE64',
                '76123456-7',
                'Proveedor SpA',
                '33',
                1234,
                '119000',
                '2026-09-01',
                'dte@proveedor.cl',
                'ACD',
                idempotencyKey: $key
            )),
            'POST',
            '/api/v1/purchase-acknowledgments',
        ];
        yield 'createCession' => [
            static fn (Client $c, ?string $key) => $c->createCession(
                new CreateCessionRequest('doc-1', '76000000-0', 'Factoring SpA', 'Av. 1', 'f@factoring.cl', idempotencyKey: $key)
            ),
            'POST',
            '/api/v1/cessions',
        ];
    }

    public function testBlankCallerIdempotencyKeyIsReplacedWithNewUuid(): void
    {
        $transport = new RecordingTransport();
        $client = new Client(new Config(apiKey: 'key', transport: $transport));

        $client->createDocument(new CreateDocumentRequest('33', '{}', idempotencyKey: '   '));

        self::assertMatchesRegularExpression(self::UUID_V4, $transport->history[0]['headers']['idempotency-key'] ?? '');
    }

    public function testRoutesWithoutIdempotencyMiddlewareOnlySendCallerKey(): void
    {
        $transport = new RecordingTransport();
        $client = new Client(new Config(apiKey: 'key', transport: $transport));

        $client->getDocuments();
        $client->generatePdf(new GeneratePdfRequest('doc-1'), false);
        $client->requeueDocument(['document_id' => 'doc-1']);
        $client->enableCertificationMode();

        foreach ($transport->history as $record) {
            self::assertArrayNotHasKey('idempotency-key', $record['headers']);
        }
    }

    public function testGenerateIdempotencyKeyReturnsDistinctUuidV4(): void
    {
        $keys = array_map(static fn (): string => Client::generateIdempotencyKey(), range(1, 50));

        foreach ($keys as $key) {
            self::assertMatchesRegularExpression(self::UUID_V4, $key);
        }
        self::assertCount(50, array_unique($keys));
    }

    public function testKeylessClientRunsOnboardingWithoutApiKey(): void
    {
        $transport = new RecordingTransport();
        $client = Client::withoutApiKey(new Config(apiKey: '', baseUrl: 'https://api.integradte.cl', transport: $transport));

        $client->getHealth();
        $client->login(new LoginRequest(email: 'a@b.cl', password: 'secret'));
        $client->createFirstBusiness(self::firstBusinessRequest(), 'user-key-1');

        $this->assertRecordedRequest($transport->history[0], 'GET', '/api/v1/health');
        $this->assertRecordedRequest($transport->history[1], 'POST', '/api/v1/auth/login', [
            'email' => 'a@b.cl',
            'password' => 'secret',
        ]);
        self::assertSame('POST', $transport->history[2]['method']);
        self::assertSame('/api/v1/onboarding/businesses', parse_url($transport->history[2]['url'], PHP_URL_PATH));
        self::assertSame('user-key-1', $transport->history[2]['headers']['x-user-key'] ?? null);

        foreach ($transport->history as $record) {
            self::assertArrayNotHasKey('x-api-key', $record['headers']);
        }
    }

    #[DataProvider('apiKeyRouteProvider')]
    public function testKeylessClientRejectsApiKeyRoutesBeforeSending(Closure $call): void
    {
        $transport = new RecordingTransport();
        $client = Client::withoutApiKey(new Config(apiKey: '', transport: $transport));

        try {
            $call($client);
            self::fail('expected BadMethodCallException');
        } catch (BadMethodCallException $e) {
            self::assertStringContainsString('Client::withoutApiKey()', $e->getMessage());
        }

        self::assertSame([], $transport->history);
    }

    /**
     * One method per port, verb and route family. The check lives in doJson, which every
     * x-api-key route goes through.
     *
     * @return iterable<string, array{0: Closure(Client): mixed}>
     */
    public static function apiKeyRouteProvider(): iterable
    {
        yield 'getMe' => [static fn (Client $c) => $c->getMe()];
        yield 'getDocument' => [static fn (Client $c) => $c->getDocument('doc-1')];
        yield 'createDocument' => [static fn (Client $c) => $c->createDocument(new CreateDocumentRequest('33', '{}'))];
        yield 'generatePdf' => [static fn (Client $c) => $c->generatePdf(new GeneratePdfRequest('doc-1'), true)];
        yield 'deleteNumeration' => [static fn (Client $c) => $c->deleteNumeration('num-1')];
        yield 'getDocuments' => [static fn (Client $c) => $c->getDocuments(['page' => 1])];
        yield 'requestNumbers' => [static fn (Client $c) => $c->requestNumbers(['document_type' => 33, 'quantity' => 4])];
        yield 'enableCertificationMode' => [static fn (Client $c) => $c->enableCertificationMode()];
        yield 'updateDocument' => [static fn (Client $c) => $c->updateDocument('doc-1', new UpdateDocumentRequest(dataDte: '{}'))];
        yield 'updateLowStockConfig' => [
            static fn (Client $c) => $c->updateLowStockConfig(new UpdateLowStockConfigRequest([new LowStockConfigItem('33', 5, 100)])),
        ];
        yield 'listNumerationRanges' => [static fn (Client $c) => $c->listNumerationRanges()];
        yield 'listBillingPlans' => [static fn (Client $c) => $c->listBillingPlans()];
        yield 'getConsumption' => [static fn (Client $c) => $c->getConsumption()];
        yield 'requeueCession' => [static fn (Client $c) => $c->requeueCession(new RequeueCessionRequest('ces-1'))];
        yield 'getCession' => [static fn (Client $c) => $c->getCession('ces-1')];
    }

    #[DataProvider('emptyApiKeyProvider')]
    public function testConstructorStillRejectsEmptyApiKey(string $apiKey): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('API key is required');

        new Client(new Config(apiKey: $apiKey, transport: new RecordingTransport()));
    }

    /** @return iterable<string, array{0: string}> */
    public static function emptyApiKeyProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'blank' => ['   '];
    }

    public function testWithoutApiKeyRejectsConfigThatHasApiKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('use new Client($config) instead');

        Client::withoutApiKey(new Config(apiKey: 'key'));
    }

    public function testWithoutApiKeyValidatesBaseUrl(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('invalid base URL');

        Client::withoutApiKey(new Config(apiKey: '', baseUrl: 'not a url'));
    }

    public function testWithoutApiKeyWorksWithDefaultConfig(): void
    {
        self::assertInstanceOf(Client::class, Client::withoutApiKey());
    }

    private const UUID_V4 ='/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';

    private static function firstBusinessRequest(): CreateFirstBusinessRequest
    {
        return new CreateFirstBusinessRequest(
            businessName: 'Empresa SpA',
            rut: '76000000-0',
            activity: 'Software',
            address: 'Av. Apoquindo 3000',
            commune: 'Las Condes',
            emailDte: 'dte@empresa.cl',
            emailContact: 'contacto@empresa.cl',
            rutLegalAgent: '12345678-9',
            fullNameLegalAgent: 'Ana Perez',
            resolutionNumberDte: '0',
            resolutionDateDte: '2014-08-22',
            resolutionNumberTicket: '0',
            resolutionTicketDate: '2014-08-22',
            region: 'Metropolitana',
        );
    }

    /**
     * @template T of CreateBusinessRequest
     * @param class-string<T> $class
     * @return T
     */
    private static function businessRequest(string $class, ?string $idempotencyKey): CreateBusinessRequest
    {
        return new $class(
            businessName: 'Empresa SpA',
            rut: '76000000-0',
            activity: 'Software',
            address: 'Av. Apoquindo 3000',
            commune: 'Las Condes',
            city: 'Santiago',
            emailDte: 'dte@empresa.cl',
            emailContact: 'contacto@empresa.cl',
            rutLegalAgent: '12345678-9',
            fullNameLegalAgent: 'Ana Perez',
            resolutionNumberDte: '0',
            resolutionDateDte: '2014-08-22',
            resolutionNumberTicket: '0',
            resolutionTicketDate: '2014-08-22',
            idempotencyKey: $idempotencyKey,
        );
    }

    /**
     * @param array{method: string, url: string, headers: array<string, string>, body: ?string} $record
     * @param array<mixed>|null $expectedBody
     * @param array<string, string>|null $expectedQuery
     */
    private function assertRecordedRequest(
        array $record,
        string $expectedMethod,
        string $expectedPath,
        ?array $expectedBody = null,
        ?array $expectedQuery = null,
    ): void {
        self::assertSame($expectedMethod, $record['method']);

        $parts = parse_url($record['url']);
        self::assertIsArray($parts);
        self::assertSame($expectedPath, $parts['path'] ?? null);

        parse_str($parts['query'] ?? '', $query);
        self::assertSame($expectedQuery ?? [], $query);

        if ($expectedBody === null) {
            self::assertNull($record['body']);
            return;
        }

        self::assertNotNull($record['body']);
        self::assertSame($expectedBody, json_decode($record['body'], true, 512, JSON_THROW_ON_ERROR));
    }
}

final class RecordingTransport implements HttpTransportInterface
{
    public string $method = '';
    public string $url = '';
    /** @var array<string, string> */
    public array $headers = [];
    public ?string $body = null;
    /** @var list<array{method: string, url: string, headers: array<string, string>, body: ?string}> */
    public array $history = [];
    /** @var list<string> */
    private array $responseBodies;

    /** @param list<string> $responseBodies */
    public function __construct(array $responseBodies = ['{"ok":true}'])
    {
        $this->responseBodies = $responseBodies;
    }

    public function send(string $method, string $url, array $headers, ?string $body): HttpResponse
    {
        $this->method = $method;
        $this->url = $url;
        $this->headers = $headers;
        $this->body = $body;
        $this->history[] = [
            'method' => $method,
            'url' => $url,
            'headers' => $headers,
            'body' => $body,
        ];

        return new HttpResponse(200, array_shift($this->responseBodies) ?? '{"ok":true}');
    }
}
