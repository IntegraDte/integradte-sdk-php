<?php

declare(strict_types=1);

namespace IntegraDte\Domain;

/**
 * Cuerpo de PUT /api/v1/documents/:id.
 *
 * `dataDte` es el DTE serializado como string JSON y gana si no esta vacio.
 * `dataDteJson` acepta el DTE como arreglo (o un string con JSON) y solo se usa si
 * `dataDte` viene vacio. Hay que enviar al menos uno de los dos.
 */
final class UpdateDocumentRequest
{
    /**
     * @param array<string, mixed>|string|null $dataDteJson
     */
    public function __construct(
        public ?string $dataDte = null,
        public array|string|null $dataDteJson = null,
        public ?string $idempotencyKey = null
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'data_dte' => $this->dataDte,
            'data_dte_json' => $this->dataDteJson,
        ], static fn ($value): bool => $value !== null);
    }
}
