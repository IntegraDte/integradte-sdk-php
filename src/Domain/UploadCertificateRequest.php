<?php

declare(strict_types=1);

namespace IntegraDte\Domain;

final class UploadCertificateRequest
{
    public function __construct(
        public string $certificate,
        public string $password,
        public string $expiredDate
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'certificate' => $this->certificate,
            'password' => $this->password,
            'expired_date' => $this->expiredDate,
        ];
    }
}
