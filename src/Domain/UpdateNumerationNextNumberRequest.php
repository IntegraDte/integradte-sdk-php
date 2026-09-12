<?php

declare(strict_types=1);

namespace IntegraDte\Domain;

/**
 * Cuerpo de PATCH /api/v1/numerations/:numerationId/next-number.
 * `nextNumber` (>= 1) es el folio que recibira el proximo documento.
 */
final class UpdateNumerationNextNumberRequest
{
    public function __construct(
        public int $nextNumber,
        public ?string $idempotencyKey = null
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'next_number' => $this->nextNumber,
        ];
    }
}
