<?php

declare(strict_types=1);

namespace IntegraDte\Domain;

/**
 * Cuerpo de POST /api/v1/purchase-acknowledgments/requeue. La ruta no exige
 * idempotency-key; si se envia, la API solo la usa para etiquetar el cobro.
 */
final class RequeuePurchaseRequest
{
    public function __construct(
        public string $purchaseId,
        public ?string $idempotencyKey = null
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'purchase_id' => $this->purchaseId,
        ];
    }
}
