<?php

declare(strict_types=1);

namespace IntegraDte\Tests;

use IntegraDte\Adapters\HttpIntegra\ApiError;
use IntegraDte\Adapters\HttpIntegra\Client;
use IntegraDte\Adapters\HttpIntegra\Config;
use IntegraDte\Adapters\HttpIntegra\HttpResponse;
use IntegraDte\Adapters\HttpIntegra\HttpTransportInterface;
use IntegraDte\Domain\CreateDocumentRequest;
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
        $this->assertRecordedRequest($transport->history[0], 'POST', '/v1/numbers/request', [
            'document_type' => 33,
            'quantity' => 4,
        ]);

        $client->requestNumerationsViaRabbitMq(['code_sii' => '33', 'quantity' => 120]);
        $this->assertRecordedRequest($transport->history[1], 'POST', '/api/v1/numerations/request-rabbitmq', [
            'code_sii' => '33',
            'quantity' => 120,
        ]);

        $client->requeueDocument(['document_id' => 'doc-1']);
        $this->assertRecordedRequest($transport->history[2], 'POST', '/api/v1/documents/requeue', [
            'document_id' => 'doc-1',
        ]);

        $client->requeueOfflineDocument(['document_id' => 'doc-offline-1']);
        $this->assertRecordedRequest($transport->history[3], 'POST', '/api/v1/documents/requeue/offline', [
            'document_id' => 'doc-offline-1',
        ]);

        $client->requeueDocumentStatus(['document_id' => 'doc-offline-1']);
        $this->assertRecordedRequest($transport->history[4], 'POST', '/api/v1/documents/requeue/status', [
            'document_id' => 'doc-offline-1',
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
