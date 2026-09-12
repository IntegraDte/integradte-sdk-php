<?php

declare(strict_types=1);

namespace IntegraDte\Application;

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
use IntegraDte\Ports\ExtendedIntegraDteApiInterface;
use IntegraDte\Ports\FullIntegraDteApiInterface;
use IntegraDte\Ports\IntegraDteApiInterface;
use BadMethodCallException;

final class Service
{
    public function __construct(private readonly IntegraDteApiInterface $api)
    {
    }

    /** @return array<string, mixed> */
    public function createDocument(CreateDocumentRequest $request): array
    {
        return $this->api->createDocument($request);
    }

    /** @return array<string, mixed> */
    public function getDocument(string $id): array
    {
        return $this->api->getDocument($id);
    }

    /**
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function getDocuments(array $filters = []): array
    {
        return $this->extendedApi()->getDocuments($filters);
    }

    /** @return array<string, mixed> */
    public function getDocumentStats(): array
    {
        return $this->api->getDocumentStats();
    }

    /**
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function getDocumentStatsWithFilters(array $filters = []): array
    {
        return $this->extendedApi()->getDocumentStatsWithFilters($filters);
    }

    /** @return array<string, mixed> */
    public function createCession(CreateCessionRequest $request): array
    {
        return $this->api->createCession($request);
    }

    /** @return array<string, mixed> */
    public function generatePdf(GeneratePdfRequest $request, bool $cedible): array
    {
        return $this->api->generatePdf($request, $cedible);
    }

    /** @return array<string, mixed> */
    public function createBusiness(CreateBusinessRequest $request): array
    {
        return $this->api->createBusiness($request);
    }

    /** @return array<string, mixed> */
    public function getBusinesses(): array
    {
        return $this->extendedApi()->getBusinesses();
    }

    /** @return array<string, mixed> */
    public function getBusiness(string $id): array
    {
        return $this->extendedApi()->getBusiness($id);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function enableProductionMode(array $payload): array
    {
        return $this->extendedApi()->enableProductionMode($payload);
    }

    /** @return array<string, mixed> */
    public function enableCertificationMode(): array
    {
        return $this->extendedApi()->enableCertificationMode();
    }

    /** @return array<string, mixed> */
    public function updateBusiness(string $id, UpdateBusinessRequest $request): array
    {
        return $this->api->updateBusiness($id, $request);
    }

    /** @return array<string, mixed> */
    public function uploadCertificate(string $businessId, UploadCertificateRequest $request): array
    {
        return $this->api->uploadCertificate($businessId, $request);
    }

    /**
     * `data.has_valid_certificate` is true when the business has a certificate that opens
     * with its stored password and is not expired.
     *
     * @return array{success: bool, message: string, data: array{has_valid_certificate: bool}}
     */
    public function getCertificateInfo(): array
    {
        return $this->api->getCertificateInfo();
    }

    /** @return array<string, mixed> */
    public function getMe(): array
    {
        return $this->api->getMe();
    }

    /** @return array<string, mixed> */
    public function getBillingBalance(): array
    {
        return $this->extendedApi()->getBillingBalance();
    }

    /**
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function getBillingPayments(array $filters = []): array
    {
        return $this->extendedApi()->getBillingPayments($filters);
    }

    /** @return array<string, mixed> */
    public function createPurchase(CreatePurchaseRequest $request): array
    {
        return $this->api->createPurchase($request);
    }

    /**
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function getPurchaseAcknowledgments(array $filters = []): array
    {
        return $this->extendedApi()->getPurchaseAcknowledgments($filters);
    }

    /** @return array<string, mixed> */
    public function getNumerationSummary(): array
    {
        return $this->api->getNumerationSummary();
    }

    /** @return array<string, mixed> */
    public function getLastUsedFolio(string $codeSii): array
    {
        return $this->api->getLastUsedFolio($codeSii);
    }

    /** @return array<string, mixed> */
    public function uploadNumeration(UploadNumerationRequest $request): array
    {
        return $this->api->uploadNumeration($request);
    }

    /**
     * `$idempotencyKey` is forwarded to the adapter only when given. The HTTP Client
     * sends a new UUID v4 when it is omitted.
     *
     * @return array<string, mixed>
     */
    public function deleteNumeration(string $id, ?string $idempotencyKey = null): array
    {
        if ($idempotencyKey === null) {
            return $this->api->deleteNumeration($id);
        }

        return $this->api->deleteNumeration($id, $idempotencyKey);
    }

    /**
     * @param array<string, mixed> $payload
     * @return list<array<string, mixed>>
     */
    public function requestNumbers(array $payload): array
    {
        return $this->extendedApi()->requestNumbers($payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function requeueDocument(array $payload): array
    {
        return $this->extendedApi()->requeueDocument($payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function requeueDocumentStatus(array $payload): array
    {
        return $this->extendedApi()->requeueDocumentStatus($payload);
    }

    /** @return array<string, mixed> */
    public function getHealth(): array
    {
        return $this->fullApi()->getHealth();
    }

    /** @return array<string, mixed> */
    public function login(LoginRequest $request): array
    {
        return $this->fullApi()->login($request);
    }

    /** @return array<string, mixed> */
    public function createFirstBusiness(CreateFirstBusinessRequest $request, string $userKey): array
    {
        return $this->fullApi()->createFirstBusiness($request, $userKey);
    }

    /** @return array<string, mixed> */
    public function updateDocument(string $id, UpdateDocumentRequest $request): array
    {
        return $this->fullApi()->updateDocument($id, $request);
    }

    /** @return array<string, mixed> */
    public function updateNumerationNextNumber(string $numerationId, UpdateNumerationNextNumberRequest $request): array
    {
        return $this->fullApi()->updateNumerationNextNumber($numerationId, $request);
    }

    /** @return array<string, mixed> */
    public function updateLowStockConfig(UpdateLowStockConfigRequest $request): array
    {
        return $this->fullApi()->updateLowStockConfig($request);
    }

    /**
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function listNumerationRanges(array $filters = []): array
    {
        return $this->fullApi()->listNumerationRanges($filters);
    }

    /** @return array<string, mixed> */
    public function requeuePurchase(RequeuePurchaseRequest $request): array
    {
        return $this->fullApi()->requeuePurchase($request);
    }

    /**
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function listBillingCharges(array $filters = []): array
    {
        return $this->fullApi()->listBillingCharges($filters);
    }

    /** @return array<string, mixed> */
    public function listBillingPlans(): array
    {
        return $this->fullApi()->listBillingPlans();
    }

    /**
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function listBillingInvoices(array $filters = []): array
    {
        return $this->fullApi()->listBillingInvoices($filters);
    }

    /** @return array<string, mixed> */
    public function previewSubscriptionUpgrade(string $planId): array
    {
        return $this->fullApi()->previewSubscriptionUpgrade($planId);
    }

    /** @return array<string, mixed> */
    public function getConsumption(): array
    {
        return $this->fullApi()->getConsumption();
    }

    /**
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function listConsumptionOverages(array $filters = []): array
    {
        return $this->fullApi()->listConsumptionOverages($filters);
    }

    /**
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function listConsumptionOperations(array $filters = []): array
    {
        return $this->fullApi()->listConsumptionOperations($filters);
    }

    /** @return array<string, mixed> */
    public function requeueCession(RequeueCessionRequest $request): array
    {
        return $this->fullApi()->requeueCession($request);
    }

    /**
     * @param array<string, scalar|null> $filters
     * @return array<string, mixed>
     */
    public function listCessions(array $filters = []): array
    {
        return $this->fullApi()->listCessions($filters);
    }

    /** @return array<string, mixed> */
    public function getCession(string $id): array
    {
        return $this->fullApi()->getCession($id);
    }

    private function extendedApi(): ExtendedIntegraDteApiInterface
    {
        if ($this->api instanceof ExtendedIntegraDteApiInterface) {
            return $this->api;
        }

        throw new BadMethodCallException('integradte: this API adapter does not support the requested extended operation');
    }

    private function fullApi(): FullIntegraDteApiInterface
    {
        if ($this->api instanceof FullIntegraDteApiInterface) {
            return $this->api;
        }

        throw new BadMethodCallException('integradte: this API adapter does not support the requested extended operation');
    }
}
