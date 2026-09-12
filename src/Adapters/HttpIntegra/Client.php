<?php

declare(strict_types=1);

namespace IntegraDte\Adapters\HttpIntegra;

use IntegraDte\Domain\CreateBusinessRequest;
use IntegraDte\Domain\CreateCessionRequest;
use IntegraDte\Domain\CreateDocumentRequest;
use IntegraDte\Domain\CreateFirstBusinessRequest;
use IntegraDte\Domain\CreatePurchaseRequest;
use IntegraDte\Domain\GeneratePdfRequest;
use IntegraDte\Domain\LoginRequest;
use IntegraDte\Domain\RequeueCessionRequest;
use IntegraDte\Domain\RequeuePurchaseRequest;
use IntegraDte\Domain\UpdateBusinessRequest;
use IntegraDte\Domain\UpdateDocumentRequest;
use IntegraDte\Domain\UpdateLowStockConfigRequest;
use IntegraDte\Domain\UpdateNumerationNextNumberRequest;
use IntegraDte\Domain\UploadCertificateRequest;
use IntegraDte\Domain\UploadNumerationRequest;
use IntegraDte\Ports\FullIntegraDteApiInterface;
use BadMethodCallException;
use InvalidArgumentException;
use JsonException;
use ReflectionClass;

final class Client implements FullIntegraDteApiInterface
{
    private readonly Config $config;
    private readonly HttpTransportInterface $transport;
    private readonly bool $hasApiKey;

    public function __construct(Config $config)
    {
        if (trim($config->apiKey) === '') {
            throw new InvalidArgumentException('integradte: API key is required');
        }

        $this->initialize($config, true);
    }

    /**
     * Builds a client for onboarding, before the user has an x-api-key: only getHealth(),
     * login() and createFirstBusiness() work, and x-api-key is never sent. Any other method
     * throws BadMethodCallException before making a request.
     *
     * `$config` (optional) sets baseUrl, userAgent, timeout or transport; its apiKey must be
     * empty, e.g. `new Config(apiKey: '', baseUrl: '...')`.
     */
    public static function withoutApiKey(?Config $config = null): self
    {
        $config ??= new Config(apiKey: '');

        if (trim($config->apiKey) !== '') {
            throw new InvalidArgumentException(
                'integradte: Client::withoutApiKey() expects a Config without API key; use new Client($config) instead'
            );
        }

        $client = (new ReflectionClass(self::class))->newInstanceWithoutConstructor();
        $client->initialize($config, false);

        return $client;
    }

    private function initialize(Config $config, bool $hasApiKey): void
    {
        if (filter_var($config->baseUrl, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('integradte: invalid base URL');
        }

        $this->config = $config;
        $this->hasApiKey = $hasApiKey;
        $this->transport = $config->transport ?? new CurlTransport($config->timeoutSeconds);
    }

    /** @return array<string, mixed> */
    public function createDocument(CreateDocumentRequest $request): array
    {
        return $this->doJson('POST', '/api/v1/documents', $request->toArray(), [], self::idempotencyKeyOrNew($request->idempotencyKey));
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
        return $this->doJson('POST', '/api/v1/cessions', $request->toArray(), [], self::idempotencyKeyOrNew($request->idempotencyKey));
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
        return $this->doJson('POST', '/api/v1/businesses', $request->toArray(), [], self::idempotencyKeyOrNew($request->idempotencyKey));
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
        return $this->doJson(
            'PUT',
            '/api/v1/businesses/' . rawurlencode($id),
            $request->toArray(),
            [],
            self::idempotencyKeyOrNew($request->idempotencyKey)
        );
    }

    /** @return array<string, mixed> */
    public function uploadCertificate(string $businessId, UploadCertificateRequest $request): array
    {
        return $this->doJson(
            'PUT',
            '/api/v1/business/' . rawurlencode($businessId) . '/certificate',
            $request->toArray(),
            [],
            self::idempotencyKeyOrNew($request->idempotencyKey)
        );
    }

    /**
     * `data.has_valid_certificate` is true when the business has a certificate that opens
     * with its stored password and is not expired.
     *
     * @return array{success: bool, message: string, data: array{has_valid_certificate: bool}}
     */
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
        return $this->doJson(
            'POST',
            '/api/v1/purchase-acknowledgments',
            $request->toArray(),
            [],
            self::idempotencyKeyOrNew($request->idempotencyKey)
        );
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
        return $this->doJson('PUT', '/api/v1/numerations', $request->toArray(), [], self::idempotencyKeyOrNew($request->idempotencyKey));
    }

    /**
     * `$idempotencyKey` is optional: without one a new UUID v4 is sent, because the route
     * requires the header.
     *
     * @return array<string, mixed>
     */
    public function deleteNumeration(string $id, ?string $idempotencyKey = null): array
    {
        return $this->doJson('DELETE', '/api/v1/numerations/' . rawurlencode($id), null, [], self::idempotencyKeyOrNew($idempotencyKey));
    }

    /**
     * @param array<string, mixed> $payload
     * @return list<array<string, mixed>>
     */
    public function requestNumbers(array $payload): array
    {
        return $this->doJson('POST', '/api/v1/numerations/request', $payload);
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
    public function requeueDocumentStatus(array $payload): array
    {
        return $this->doJson('POST', '/api/v1/documents/requeue/status', $payload);
    }

    /**
     * GET /api/v1/health, without authentication. Returns the raw JSON (`service`,
     * `started_at`, `uptime_seconds`, ...), not the `{success, message, data}` envelope.
     *
     * @return array<string, mixed>
     */
    public function getHealth(): array
    {
        return $this->doJson('GET', '/api/v1/health', null, [], null, []);
    }

    /**
     * POST /api/v1/auth/login, without x-api-key. `data.xUserKey` is the `x-user-key`
     * for createFirstBusiness.
     *
     * @return array<string, mixed>
     */
    public function login(LoginRequest $request): array
    {
        return $this->doJson('POST', '/api/v1/auth/login', $request->toArray(), [], null, []);
    }

    /**
     * POST /api/v1/onboarding/businesses, authenticated with the `x-user-key` from login
     * (not the configured x-api-key). `data.apiToken.xApiKey` is the x-api-key to use from
     * then on.
     *
     * @return array<string, mixed>
     */
    public function createFirstBusiness(CreateFirstBusinessRequest $request, string $userKey): array
    {
        if (trim($userKey) === '') {
            throw new InvalidArgumentException('integradte: user key is required');
        }

        return $this->doJson('POST', '/api/v1/onboarding/businesses', $request->toArray(), [], null, ['x-user-key' => $userKey]);
    }

    /**
     * The API never stores this route's response for replay: reusing a key returns 500,
     * so use a new key per attempt (the default when none is given).
     *
     * @return array<string, mixed>
     */
    public function updateDocument(string $id, UpdateDocumentRequest $request): array
    {
        return $this->doJson(
            'PUT',
            '/api/v1/documents/' . rawurlencode($id),
            $request->toArray(),
            [],
            self::idempotencyKeyOrNew($request->idempotencyKey)
        );
    }

    /**
     * `$numerationId` is the CAF range id (`ranges[].id` from listNumerationRanges).
     *
     * @return array<string, mixed>
     */
    public function updateNumerationNextNumber(string $numerationId, UpdateNumerationNextNumberRequest $request): array
    {
        return $this->doJson(
            'PATCH',
            '/api/v1/numerations/' . rawurlencode($numerationId) . '/next-number',
            $request->toArray(),
            [],
            self::idempotencyKeyOrNew($request->idempotencyKey)
        );
    }

    /** @return array<string, mixed> */
    public function updateLowStockConfig(UpdateLowStockConfigRequest $request): array
    {
        return $this->doJson(
            'PATCH',
            '/api/v1/numerations/low-stock',
            $request->toArray(),
            [],
            self::idempotencyKeyOrNew($request->idempotencyKey)
        );
    }

    /**
     * Filters: `code_sii`. The order of `data.items` is not guaranteed.
     *
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function listNumerationRanges(array $filters = []): array
    {
        return $this->doJson('GET', '/api/v1/numerations/ranges', null, $filters);
    }

    /** @return array<string, mixed> */
    public function requeuePurchase(RequeuePurchaseRequest $request): array
    {
        return $this->doJson('POST', '/api/v1/purchase-acknowledgments/requeue', $request->toArray(), [], $request->idempotencyKey);
    }

    /**
     * Filters: `page`, `limit`, `status`, `pricing_key`, `from_date`, `to_date`.
     *
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function listBillingCharges(array $filters = []): array
    {
        return $this->doJson('GET', '/api/v1/billing/charges', null, $filters);
    }

    /**
     * `data` is a bare list of active plans.
     *
     * @return array<string, mixed>
     */
    public function listBillingPlans(): array
    {
        return $this->doJson('GET', '/api/v1/billing/plans');
    }

    /**
     * Filters: `status`. `data` is a bare list, sorted by period descending.
     *
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function listBillingInvoices(array $filters = []): array
    {
        return $this->doJson('GET', '/api/v1/billing/invoices', null, $filters);
    }

    /**
     * `$planId` accepts the plan id or its `code`. Read-only: nothing is charged.
     *
     * @return array<string, mixed>
     */
    public function previewSubscriptionUpgrade(string $planId): array
    {
        return $this->doJson('GET', '/api/v1/billing/subscription/upgrade/preview', null, ['plan_id' => $planId]);
    }

    /** @return array<string, mixed> */
    public function getConsumption(): array
    {
        return $this->doJson('GET', '/api/v1/consumption');
    }

    /**
     * Filters: `page`, `limit`.
     *
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function listConsumptionOverages(array $filters = []): array
    {
        return $this->doJson('GET', '/api/v1/consumption/overages', null, $filters);
    }

    /**
     * Filters: `period` (`YYYY-MM`, current UTC month by default). Not paginated.
     *
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function listConsumptionOperations(array $filters = []): array
    {
        return $this->doJson('GET', '/api/v1/consumption/operations', null, $filters);
    }

    /** @return array<string, mixed> */
    public function requeueCession(RequeueCessionRequest $request): array
    {
        return $this->doJson('POST', '/api/v1/cessions/requeue', $request->toArray(), [], $request->idempotencyKey);
    }

    /**
     * Filters: `page`, `limit`, `document_id`. The list comes under `data.cessions`.
     *
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function listCessions(array $filters = []): array
    {
        return $this->doJson('GET', '/api/v1/cessions', null, $filters);
    }

    /** @return array<string, mixed> */
    public function getCession(string $id): array
    {
        return $this->doJson('GET', '/api/v1/cessions/' . rawurlencode($id));
    }

    /**
     * Returns a random UUID v4, the format the API accepts in `idempotency-key`.
     */
    public static function generateIdempotencyKey(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }

    /**
     * Routes behind the API's IdempotencyMiddleware reject requests without
     * `idempotency-key`, so they always get one: the caller's, or a new UUID v4.
     */
    private static function idempotencyKeyOrNew(?string $idempotencyKey): string
    {
        if ($idempotencyKey !== null && trim($idempotencyKey) !== '') {
            return $idempotencyKey;
        }

        return self::generateIdempotencyKey();
    }

    /**
     * @param array<string, mixed>|null $body
     * @param array<string, scalar|null> $query
     * @param array<string, string>|null $authHeaders null sends the configured x-api-key; [] sends no credentials
     * @return array<string, mixed>
     */
    private function doJson(
        string $method,
        string $route,
        ?array $body = null,
        array $query = [],
        ?string $idempotencyKey = null,
        ?array $authHeaders = null
    ): array {
        if ($authHeaders === null) {
            if (!$this->hasApiKey) {
                throw new BadMethodCallException(sprintf(
                    'integradte: %s %s needs x-api-key, but this client was built with Client::withoutApiKey(), '
                    . 'which only supports getHealth(), login() and createFirstBusiness(); '
                    . 'use new Client(new Config(apiKey: ...))',
                    $method,
                    $route
                ));
            }

            $authHeaders = ['x-api-key' => $this->config->apiKey];
        }

        $url = $this->buildUrl($route, $query);
        $headers = $authHeaders + [
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
