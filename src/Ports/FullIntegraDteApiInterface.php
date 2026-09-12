<?php

declare(strict_types=1);

namespace IntegraDte\Ports;

use IntegraDte\Domain\CreateFirstBusinessRequest;
use IntegraDte\Domain\LoginRequest;
use IntegraDte\Domain\RequeueCessionRequest;
use IntegraDte\Domain\RequeuePurchaseRequest;
use IntegraDte\Domain\UpdateDocumentRequest;
use IntegraDte\Domain\UpdateLowStockConfigRequest;
use IntegraDte\Domain\UpdateNumerationNextNumberRequest;

/**
 * Resto de la API publica. Es un puerto aparte (y no metodos nuevos en
 * IntegraDteApiInterface / ExtendedIntegraDteApiInterface) para no romper a quienes
 * ya implementan esos contratos.
 */
interface FullIntegraDteApiInterface extends ExtendedIntegraDteApiInterface
{
    /**
     * GET /api/v1/health, sin autenticacion. Devuelve el JSON crudo, sin envoltorio
     * `{success, message, data}`.
     *
     * @return array<string, mixed>
     */
    public function getHealth(): array;

    /** @return array<string, mixed> */
    public function login(LoginRequest $request): array;

    /** @return array<string, mixed> */
    public function createFirstBusiness(CreateFirstBusinessRequest $request, string $userKey): array;

    /** @return array<string, mixed> */
    public function updateDocument(string $id, UpdateDocumentRequest $request): array;

    /** @return array<string, mixed> */
    public function updateNumerationNextNumber(string $numerationId, UpdateNumerationNextNumberRequest $request): array;

    /** @return array<string, mixed> */
    public function updateLowStockConfig(UpdateLowStockConfigRequest $request): array;

    /**
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function listNumerationRanges(array $filters = []): array;

    /** @return array<string, mixed> */
    public function requeuePurchase(RequeuePurchaseRequest $request): array;

    /**
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function listBillingCharges(array $filters = []): array;

    /** @return array<string, mixed> */
    public function listBillingPlans(): array;

    /**
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function listBillingInvoices(array $filters = []): array;

    /** @return array<string, mixed> */
    public function previewSubscriptionUpgrade(string $planId): array;

    /** @return array<string, mixed> */
    public function getConsumption(): array;

    /**
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function listConsumptionOverages(array $filters = []): array;

    /**
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function listConsumptionOperations(array $filters = []): array;

    /** @return array<string, mixed> */
    public function requeueCession(RequeueCessionRequest $request): array;

    /**
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function listCessions(array $filters = []): array;

    /** @return array<string, mixed> */
    public function getCession(string $id): array;
}
