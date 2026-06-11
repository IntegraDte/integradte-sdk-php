<?php

declare(strict_types=1);

namespace IntegraDte\Application;

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

    /** @return array<string, mixed> */
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

    /** @return array<string, mixed> */
    public function deleteNumeration(string $id): array
    {
        return $this->api->deleteNumeration($id);
    }

    /** @return array<string, mixed> */
    public function getCurrentCertificate(): array
    {
        return $this->extendedApi()->getCurrentCertificate();
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createLicense(array $payload): array
    {
        return $this->extendedApi()->createLicense($payload);
    }

    /** @return array<string, mixed> */
    public function getLicenses(): array
    {
        return $this->extendedApi()->getLicenses();
    }

    /** @return array<string, mixed> */
    public function getLicense(string $id): array
    {
        return $this->extendedApi()->getLicense($id);
    }

    /** @return array<string, mixed> */
    public function getLicenseDevices(string $id): array
    {
        return $this->extendedApi()->getLicenseDevices($id);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function enableLicense(string $id, array $payload = []): array
    {
        return $this->extendedApi()->enableLicense($id, $payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function disableLicense(string $id, array $payload = []): array
    {
        return $this->extendedApi()->disableLicense($id, $payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function revokeLicense(string $id, array $payload = []): array
    {
        return $this->extendedApi()->revokeLicense($id, $payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function activateLicense(array $payload): array
    {
        return $this->extendedApi()->activateLicense($payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function refreshLicense(array $payload): array
    {
        return $this->extendedApi()->refreshLicense($payload);
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
    public function requestNumerationsViaRabbitMq(array $payload): array
    {
        return $this->extendedApi()->requestNumerationsViaRabbitMq($payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function syncDocument(array $payload): array
    {
        return $this->extendedApi()->syncDocument($payload);
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
    public function requeueOfflineDocument(array $payload): array
    {
        return $this->extendedApi()->requeueOfflineDocument($payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function requeueDocumentStatus(array $payload): array
    {
        return $this->extendedApi()->requeueDocumentStatus($payload);
    }

    private function extendedApi(): ExtendedIntegraDteApiInterface
    {
        if ($this->api instanceof ExtendedIntegraDteApiInterface) {
            return $this->api;
        }

        throw new BadMethodCallException('integradte: this API adapter does not support the requested extended operation');
    }
}
