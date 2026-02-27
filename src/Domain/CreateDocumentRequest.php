<?php

declare(strict_types=1);

namespace IntegraFacturacion\Domain;

final class CreateDocumentRequest
{
    public function __construct(
        public string $codeSii,
        public string $dataDte,
        public ?string $userId = null,
        public ?string $businessId = null,
        public ?string $idempotencyKey = null
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'user_id' => $this->userId,
            'business_id' => $this->businessId,
            'code_sii' => $this->codeSii,
            'data_dte' => $this->dataDte,
        ], static fn ($value): bool => $value !== null);
    }
}
