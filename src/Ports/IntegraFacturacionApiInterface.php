<?php

declare(strict_types=1);

namespace IntegraFacturacion\Ports;

use IntegraFacturacion\Domain\CreateBusinessRequest;
use IntegraFacturacion\Domain\CreateCessionRequest;
use IntegraFacturacion\Domain\CreateDocumentRequest;
use IntegraFacturacion\Domain\CreatePurchaseRequest;
use IntegraFacturacion\Domain\GeneratePdfRequest;
use IntegraFacturacion\Domain\UpdateBusinessRequest;
use IntegraFacturacion\Domain\UploadCertificateRequest;
use IntegraFacturacion\Domain\UploadNumerationRequest;

interface IntegraFacturacionApiInterface
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
