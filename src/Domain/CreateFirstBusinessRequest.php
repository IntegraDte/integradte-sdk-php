<?php

declare(strict_types=1);

namespace IntegraDte\Domain;

/**
 * Cuerpo de POST /api/v1/onboarding/businesses (crear la PRIMERA empresa con x-user-key).
 *
 * La API exige `region` o `city` (al menos uno). Las fechas de resolucion van como
 * `YYYY-MM-DD` o RFC 3339. `logo` es base64 sin prefijo `data:`. Esta ruta no usa
 * idempotency-key.
 */
final class CreateFirstBusinessRequest
{
    public function __construct(
        public string $businessName,
        public string $rut,
        public string $activity,
        public string $address,
        public string $commune,
        public string $emailDte,
        public string $emailContact,
        public string $rutLegalAgent,
        public string $fullNameLegalAgent,
        public string $resolutionNumberDte,
        public string $resolutionDateDte,
        public string $resolutionNumberTicket,
        public string $resolutionTicketDate,
        public ?string $region = null,
        public ?string $city = null,
        public ?string $logo = null,
        public ?string $logoContentType = null
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'businessName' => $this->businessName,
            'rut' => $this->rut,
            'activity' => $this->activity,
            'address' => $this->address,
            'commune' => $this->commune,
            'region' => $this->region,
            'city' => $this->city,
            'emailDte' => $this->emailDte,
            'emailContact' => $this->emailContact,
            'rutLegalAgent' => $this->rutLegalAgent,
            'fullNameLegalAgent' => $this->fullNameLegalAgent,
            'resolutionNumberDte' => $this->resolutionNumberDte,
            'resolutionDateDte' => $this->resolutionDateDte,
            'resolutionNumberTicket' => $this->resolutionNumberTicket,
            'resolutionTicketDate' => $this->resolutionTicketDate,
            'logo' => $this->logo,
            'logoContentType' => $this->logoContentType,
        ], static fn ($value): bool => $value !== null);
    }
}
