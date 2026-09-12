<?php

declare(strict_types=1);

namespace IntegraDte\Domain;

/**
 * Umbral de folios bajos para un tipo de DTE. `codeSii` va como string
 * (33, 34, 39, 41, 46, 52, 56 o 61), `threshold` >= 0 y `requestQuantity` >= 1.
 */
final class LowStockConfigItem
{
    public function __construct(
        public string $codeSii,
        public int $threshold,
        public int $requestQuantity
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'code_sii' => $this->codeSii,
            'threshold' => $this->threshold,
            'request_quantity' => $this->requestQuantity,
        ];
    }
}
