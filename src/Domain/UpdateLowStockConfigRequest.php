<?php

declare(strict_types=1);

namespace IntegraDte\Domain;

/**
 * Cuerpo de PATCH /api/v1/numerations/low-stock. La API mezcla por `code_sii`:
 * los codigos que no vienen en `items` conservan su configuracion.
 */
final class UpdateLowStockConfigRequest
{
    /**
     * @param list<LowStockConfigItem> $items
     */
    public function __construct(
        public array $items,
        public ?string $idempotencyKey = null
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'items' => array_map(
                static fn (LowStockConfigItem $item): array => $item->toArray(),
                array_values($this->items)
            ),
        ];
    }
}
