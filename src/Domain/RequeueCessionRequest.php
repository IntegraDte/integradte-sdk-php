<?php

declare(strict_types=1);

namespace IntegraDte\Domain;

/**
 * Cuerpo de POST /api/v1/cessions/requeue. La ruta no exige idempotency-key;
 * si se envia, la API solo la usa para etiquetar el cobro.
 */
final class RequeueCessionRequest
{
    public function __construct(
        public string $cessionId,
        public ?string $idempotencyKey = null
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'cession_id' => $this->cessionId,
        ];
    }
}
