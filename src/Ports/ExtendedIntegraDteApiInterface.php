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

    /**
     * @param array<string, mixed> $payload
     * @return list<array<string, mixed>>
     */
    public function requestNumbers(array $payload): array;

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function requeueDocument(array $payload): array;

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function requeueDocumentStatus(array $payload): array;
}
