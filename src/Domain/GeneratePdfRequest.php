<?php

declare(strict_types=1);

namespace IntegraDte\Domain;

final class GeneratePdfRequest
{
    public function __construct(
        public string $documentId,
        public ?string $formato = null,
        public ?bool $copiaCedible = null,
        public ?string $idempotencyKey = null
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'document_id' => $this->documentId,
            'formato' => $this->formato,
            'copia_cedible' => $this->copiaCedible,
        ], static fn ($value): bool => $value !== null);
    }
}
