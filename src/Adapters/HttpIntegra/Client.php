<?php

declare(strict_types=1);

namespace IntegraFacturacion\Adapters\HttpIntegra;

use IntegraFacturacion\Domain\CreateBusinessRequest;
use IntegraFacturacion\Domain\CreateCessionRequest;
use IntegraFacturacion\Domain\CreateDocumentRequest;
use IntegraFacturacion\Domain\CreatePurchaseRequest;
use IntegraFacturacion\Domain\GeneratePdfRequest;
use IntegraFacturacion\Domain\UpdateBusinessRequest;
use IntegraFacturacion\Domain\UploadCertificateRequest;
use IntegraFacturacion\Domain\UploadNumerationRequest;
use IntegraFacturacion\Ports\IntegraFacturacionApiInterface;
use InvalidArgumentException;
use JsonException;

final class Client implements IntegraFacturacionApiInterface
{
    private readonly HttpTransportInterface $transport;

    public function __construct(private readonly Config $config)
    {
        if (trim($this->config->apiKey) === '') {
            throw new InvalidArgumentException('integrafacturacion: API key is required');
        }

        if (filter_var($this->config->baseUrl, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('integrafacturacion: invalid base URL');
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

    /** @return array<string, mixed> */
    public function getDocumentStats(): array
    {
        return $this->doJson('GET', '/api/v1/documents/stats');
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
    public function createPurchase(CreatePurchaseRequest $request): array
    {
        return $this->doJson('POST', '/api/v1/purchases', $request->toArray(), [], $request->idempotencyKey);
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

    /**
     * @param array<string, mixed>|null $body
     * @param array<string, string> $query
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
                throw new InvalidArgumentException('integrafacturacion: invalid JSON body: ' . $e->getMessage(), 0, $e);
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
            throw new InvalidArgumentException('integrafacturacion: invalid response JSON: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param array<string, string> $query
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
            throw new InvalidArgumentException('integrafacturacion: invalid DTE payload: ' . $e->getMessage(), 0, $e);
        }
    }
}
