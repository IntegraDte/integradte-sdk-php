<?php

declare(strict_types=1);

namespace IntegraDte\Adapters\HttpIntegra;

use IntegraDte\Domain\CreateBusinessRequest;
use IntegraDte\Domain\CreateCessionRequest;
use IntegraDte\Domain\CreateDocumentRequest;
use IntegraDte\Domain\CreatePurchaseRequest;
use IntegraDte\Domain\GeneratePdfRequest;
use IntegraDte\Domain\UpdateBusinessRequest;
use IntegraDte\Domain\UploadCertificateRequest;
use IntegraDte\Domain\UploadNumerationRequest;
use IntegraDte\Ports\ExtendedIntegraDteApiInterface;
use InvalidArgumentException;
use JsonException;

final class Client implements ExtendedIntegraDteApiInterface
{
    private readonly HttpTransportInterface $transport;

    public function __construct(private readonly Config $config)
    {
        if (trim($this->config->apiKey) === '') {
            throw new InvalidArgumentException('integradte: API key is required');
        }

        if (filter_var($this->config->baseUrl, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('integradte: invalid base URL');
        }

        $this->transport = $this->config->transport ?? new CurlTransport($this->config->timeoutSeconds);
    }

    /** @return array<string, mixed> */
    public function createDocument(CreateDocumentRequest $request): array
    {
        return $this->doJson('POST', '/api/v1/documents', $request->toArray(), [], $request->idempotencyKey);
    }

    /** @return array<string, mixed> */
    public function getDocument(string $id): array
    {
        return $this->doJson('GET', '/api/v1/documents/' . rawurlencode($id));
    }

    /**
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function getDocuments(array $filters = []): array
    {
        return $this->doJson('GET', '/api/v1/documents', null, $filters);
    }

    /** @return array<string, mixed> */
    public function getDocumentStats(): array
    {
        return $this->doJson('GET', '/api/v1/documents/stats');
    }

    /**
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function getDocumentStatsWithFilters(array $filters = []): array
    {
        return $this->doJson('GET', '/api/v1/documents/stats', null, $filters);
    }

    /** @return array<string, mixed> */
    public function createCession(CreateCessionRequest $request): array
    {
        return $this->doJson('POST', '/api/v1/cessions', $request->toArray(), [], $request->idempotencyKey);
    }

    /** @return array<string, mixed> */
    public function generatePdf(GeneratePdfRequest $request, bool $cedible): array
    {
        return $this->doJson(
            'POST',
            '/api/v1/pdfs/generate',
            $request->toArray(),
            ['cedible' => $cedible ? 'true' : 'false'],
            $request->idempotencyKey
        );
    }

    /** @return array<string, mixed> */
    public function createBusiness(CreateBusinessRequest $request): array
    {
        return $this->doJson('POST', '/api/v1/businesses', $request->toArray(), [], $request->idempotencyKey);
    }

    /** @return array<string, mixed> */
    public function getBusinesses(): array
    {
        return $this->doJson('GET', '/api/v1/businesses');
    }

    /** @return array<string, mixed> */
    public function getBusiness(string $id): array
    {
        return $this->doJson('GET', '/api/v1/businesses/' . rawurlencode($id));
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function enableProductionMode(array $payload): array
    {
        return $this->doJson('POST', '/api/v1/businesses/production-mode', $payload);
    }

    /** @return array<string, mixed> */
    public function enableCertificationMode(): array
    {
        return $this->doJson('POST', '/api/v1/businesses/certification-mode');
    }

    /** @return array<string, mixed> */
    public function updateBusiness(string $id, UpdateBusinessRequest $request): array
    {
        return $this->doJson('PUT', '/api/v1/businesses/' . rawurlencode($id), $request->toArray(), [], $request->idempotencyKey);
    }

    /** @return array<string, mixed> */
    public function uploadCertificate(string $businessId, UploadCertificateRequest $request): array
    {
        return $this->doJson('PUT', '/api/v1/business/' . rawurlencode($businessId) . '/certificate', $request->toArray());
    }

    /** @return array<string, mixed> */
    public function getCertificateInfo(): array
    {
        return $this->doJson('GET', '/api/v1/business/certificate-info');
    }

    /** @return array<string, mixed> */
    public function getMe(): array
    {
        return $this->doJson('GET', '/api/v1/users/me');
    }

    /** @return array<string, mixed> */
    public function getBillingBalance(): array
    {
        return $this->doJson('GET', '/api/v1/billing/balance');
    }

    /**
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function getBillingPayments(array $filters = []): array
    {
        return $this->doJson('GET', '/api/v1/billing/payments', null, $filters);
    }

    /** @return array<string, mixed> */
    public function createPurchase(CreatePurchaseRequest $request): array
    {
        return $this->doJson('POST', '/api/v1/purchases', $request->toArray(), [], $request->idempotencyKey);
    }

    /**
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function getPurchaseAcknowledgments(array $filters = []): array
    {
        return $this->doJson('GET', '/api/v1/purchase-acknowledgments', null, $filters);
    }

    /** @return array<string, mixed> */
    public function getNumerationSummary(): array
    {
        return $this->doJson('GET', '/api/v1/numerations/summary');
    }

    /** @return array<string, mixed> */
    public function getLastUsedFolio(string $codeSii): array
    {
        return $this->doJson('GET', '/api/v1/numerations/last-used-number', null, ['code_sii' => $codeSii]);
    }

    /** @return array<string, mixed> */
    public function uploadNumeration(UploadNumerationRequest $request): array
    {
        return $this->doJson('PUT', '/api/v1/numerations', $request->toArray());
    }

    /** @return array<string, mixed> */
    public function deleteNumeration(string $id): array
    {
        return $this->doJson('DELETE', '/api/v1/numerations/' . rawurlencode($id));
    }

    /** @return array<string, mixed> */
    public function getCurrentCertificate(): array
    {
        return $this->doJson('GET', '/api/v1/certificates/current');
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createLicense(array $payload): array
    {
        return $this->doJson('POST', '/api/v1/licenses', $payload);
    }

    /** @return array<string, mixed> */
    public function getLicenses(): array
    {
        return $this->doJson('GET', '/api/v1/licenses');
    }

    /** @return array<string, mixed> */
    public function getLicense(string $id): array
    {
        return $this->doJson('GET', '/api/v1/licenses/' . rawurlencode($id));
    }

    /** @return array<string, mixed> */
    public function getLicenseDevices(string $id): array
    {
        return $this->doJson('GET', '/api/v1/licenses/' . rawurlencode($id) . '/devices');
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function enableLicense(string $id, array $payload = []): array
    {
        return $this->doJson('POST', '/api/v1/licenses/' . rawurlencode($id) . '/enable', $payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function disableLicense(string $id, array $payload = []): array
    {
        return $this->doJson('POST', '/api/v1/licenses/' . rawurlencode($id) . '/disable', $payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function revokeLicense(string $id, array $payload = []): array
    {
        return $this->doJson('POST', '/api/v1/licenses/' . rawurlencode($id) . '/revoke', $payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function activateLicense(array $payload): array
    {
        return $this->doJson('POST', '/api/v1/licenses/activate', $payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function refreshLicense(array $payload): array
    {
        return $this->doJson('POST', '/api/v1/licenses/refresh', $payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @return list<array<string, mixed>>
     */
    public function requestNumbers(array $payload): array
    {
        return $this->doJson('POST', '/v1/numbers/request', $payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function requestNumerationsViaRabbitMq(array $payload): array
    {
        return $this->doJson('POST', '/api/v1/numerations/request-rabbitmq', $payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function syncDocument(array $payload): array
    {
        return $this->doJson('POST', '/api/v1/documents/sync', $payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function requeueDocument(array $payload): array
    {
        return $this->doJson('POST', '/api/v1/documents/requeue', $payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function requeueOfflineDocument(array $payload): array
    {
        return $this->doJson('POST', '/api/v1/documents/requeue/offline', $payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function requeueDocumentStatus(array $payload): array
    {
        return $this->doJson('POST', '/api/v1/documents/requeue/status', $payload);
    }

    /**
     * @param array<string, mixed>|null $body
     * @param array<string, scalar|null> $query
     * @return array<string, mixed>
     */
    private function doJson(string $method, string $route, ?array $body = null, array $query = [], ?string $idempotencyKey = null): array
    {
        $url = $this->buildUrl($route, $query);
        $headers = [
            'x-api-key' => $this->config->apiKey,
            'Accept' => 'application/json',
            'User-Agent' => $this->config->userAgent,
        ];

        $payload = null;
        if ($body !== null) {
            try {
                $payload = json_encode($body, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw new InvalidArgumentException('integradte: invalid JSON body: ' . $e->getMessage(), 0, $e);
            }

            $headers['Content-Type'] = 'application/json';
        }

        if ($idempotencyKey !== null && trim($idempotencyKey) !== '') {
            $headers['idempotency-key'] = $idempotencyKey;
        }

        $response = $this->transport->send($method, $url, $headers, $payload);

        if ($response->statusCode < 200 || $response->statusCode >= 300) {
            throw new ApiError($response->statusCode, $response->body);
        }

        if ($response->body === '') {
            return [];
        }

        try {
            /** @var array<string, mixed> $decoded */
            $decoded = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);
            return $decoded;
        } catch (JsonException $e) {
            throw new InvalidArgumentException('integradte: invalid response JSON: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param array<string, scalar|null> $query
     */
    private function buildUrl(string $route, array $query = []): string
    {
        $base = rtrim($this->config->baseUrl, '/');
        $path = '/' . ltrim($route, '/');

        if ($query === []) {
            return $base . $path;
        }

        return $base . $path . '?' . http_build_query($query);
    }

    /**
     * @param array<string, mixed>|object $payload
     */
    public static function encodeDataDte(array|object $payload): string
    {
        try {
            return json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidArgumentException('integradte: invalid DTE payload: ' . $e->getMessage(), 0, $e);
        }
    }
}
