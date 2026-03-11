<?php

declare(strict_types=1);

namespace IntegraDte\Ports;

use IntegraDte\Domain\CreateBusinessRequest;
use IntegraDte\Domain\CreateCessionRequest;
use IntegraDte\Domain\CreateDocumentRequest;
use IntegraDte\Domain\CreatePurchaseRequest;
use IntegraDte\Domain\GeneratePdfRequest;
use IntegraDte\Domain\UpdateBusinessRequest;
use IntegraDte\Domain\UploadCertificateRequest;
use IntegraDte\Domain\UploadNumerationRequest;

interface IntegraDteApiInterface
{
    /** @return array<string, mixed> */
    public function createDocument(CreateDocumentRequest $request): array;

    /** @return array<string, mixed> */
    public function getDocument(string $id): array;

    /** @return array<string, mixed> */
    public function getDocumentStats(): array;

    /** @return array<string, mixed> */
    public function createCession(CreateCessionRequest $request): array;

    /** @return array<string, mixed> */
    public function generatePdf(GeneratePdfRequest $request, bool $cedible): array;

    /** @return array<string, mixed> */
    public function createBusiness(CreateBusinessRequest $request): array;

    /** @return array<string, mixed> */
    public function updateBusiness(string $id, UpdateBusinessRequest $request): array;

    /** @return array<string, mixed> */
    public function uploadCertificate(string $businessId, UploadCertificateRequest $request): array;

    /** @return array<string, mixed> */
    public function getCertificateInfo(): array;

    /** @return array<string, mixed> */
    public function getMe(): array;

    /** @return array<string, mixed> */
    public function createPurchase(CreatePurchaseRequest $request): array;

    /** @return array<string, mixed> */
    public function getNumerationSummary(): array;

    /** @return array<string, mixed> */
    public function getLastUsedFolio(string $codeSii): array;

    /** @return array<string, mixed> */
    public function uploadNumeration(UploadNumerationRequest $request): array;

    /** @return array<string, mixed> */
    public function deleteNumeration(string $id): array;
}
