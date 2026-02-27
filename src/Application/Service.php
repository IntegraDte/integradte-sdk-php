<?php

declare(strict_types=1);

namespace IntegraFacturacion\Application;

use IntegraFacturacion\Domain\CreateBusinessRequest;
use IntegraFacturacion\Domain\CreateCessionRequest;
use IntegraFacturacion\Domain\CreateDocumentRequest;
use IntegraFacturacion\Domain\CreatePurchaseRequest;
use IntegraFacturacion\Domain\GeneratePdfRequest;
use IntegraFacturacion\Domain\UpdateBusinessRequest;
use IntegraFacturacion\Domain\UploadCertificateRequest;
use IntegraFacturacion\Domain\UploadNumerationRequest;
use IntegraFacturacion\Ports\IntegraFacturacionApiInterface;

final class Service
{
    public function __construct(private readonly IntegraFacturacionApiInterface $api)
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

    /** @return array<string, mixed> */
    public function getDocumentStats(): array
    {
        return $this->api->getDocumentStats();
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
    public function createPurchase(CreatePurchaseRequest $request): array
    {
        return $this->api->createPurchase($request);
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
}
