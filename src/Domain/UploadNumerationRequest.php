<?php

declare(strict_types=1);

namespace IntegraDte\Domain;

final class UploadNumerationRequest
{
    public function __construct(
        public string $codeSii,
        public int $startNumber,
        public int $endNumber,
        public string $cafBase64,
        public string $creationDate,
        public string $dueDate
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'code_sii' => $this->codeSii,
            'start_number' => $this->startNumber,
            'end_number' => $this->endNumber,
            'caf_base64' => $this->cafBase64,
            'creation_date' => $this->creationDate,
            'due_date' => $this->dueDate,
        ];
    }
}
