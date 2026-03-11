<?php

declare(strict_types=1);

namespace IntegraDte\Domain;

final class CreatePurchaseRequest
{
    public function __construct(
        public string $xmlBase64,
        public string $rutEmisor,
        public string $razonSocialEmisor,
        public string $tipoDte,
        public int $folio,
        public string $mntTotal,
        public string $fechaEmision,
        public string $emailEmisor,
        public string $accionDoc,
        public ?string $idempotencyKey = null
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'xml_base64' => $this->xmlBase64,
            'rut_emisor' => $this->rutEmisor,
            'razon_social_emisor' => $this->razonSocialEmisor,
            'tipo_dte' => $this->tipoDte,
            'folio' => $this->folio,
            'mnt_total' => $this->mntTotal,
            'fecha_emision' => $this->fechaEmision,
            'email_emisor' => $this->emailEmisor,
            'accion_doc' => $this->accionDoc,
        ];
    }
}
