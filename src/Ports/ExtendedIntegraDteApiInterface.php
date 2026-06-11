<?php

declare(strict_types=1);

namespace IntegraDte\Ports;

interface ExtendedIntegraDteApiInterface extends IntegraDteApiInterface
{
    /**
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function getDocuments(array $filters = []): array;

    /**
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function getDocumentStatsWithFilters(array $filters = []): array;

    /** @return array<string, mixed> */
    public function getBusinesses(): array;

    /** @return array<string, mixed> */
    public function getBusiness(string $id): array;

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function enableProductionMode(array $payload): array;

    /** @return array<string, mixed> */
    public function enableCertificationMode(): array;

    /** @return array<string, mixed> */
    public function getBillingBalance(): array;

    /**
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function getBillingPayments(array $filters = []): array;

    /**
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function getPurchaseAcknowledgments(array $filters = []): array;

    /** @return array<string, mixed> */
    public function getCurrentCertificate(): array;

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createLicense(array $payload): array;

    /** @return array<string, mixed> */
    public function getLicenses(): array;

    /** @return array<string, mixed> */
    public function getLicense(string $id): array;

    /** @return array<string, mixed> */
    public function getLicenseDevices(string $id): array;

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function enableLicense(string $id, array $payload = []): array;

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function disableLicense(string $id, array $payload = []): array;

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function revokeLicense(string $id, array $payload = []): array;

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function activateLicense(array $payload): array;

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function refreshLicense(array $payload): array;

    /**
     * @param array<string, mixed> $payload
     * @return list<array<string, mixed>>
     */
    public function requestNumbers(array $payload): array;

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function requestNumerationsViaRabbitMq(array $payload): array;

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function syncDocument(array $payload): array;

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function requeueDocument(array $payload): array;

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function requeueOfflineDocument(array $payload): array;

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function requeueDocumentStatus(array $payload): array;
}
