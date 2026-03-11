<?php

declare(strict_types=1);

namespace IntegraDte\Domain;

final class CreateCessionRequest
{
    public function __construct(
        public string $documentId,
        public string $factoringCode,
        public string $factoringName,
        public string $factoringAddress,
        public string $factoringEmail,
        public ?string $idempotencyKey = null
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'document_id' => $this->documentId,
            'factoring_code' => $this->factoringCode,
            'factoring_name' => $this->factoringName,
            'factoring_address' => $this->factoringAddress,
            'factoring_email' => $this->factoringEmail,
        ];
    }
}
