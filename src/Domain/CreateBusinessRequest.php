<?php

declare(strict_types=1);

namespace IntegraDte\Domain;

final class CreateBusinessRequest
{
    public function __construct(
        public string $businessName,
        public string $rut,
        public string $activity,
        public string $address,
        public string $commune,
        public string $city,
        public string $emailDte,
        public string $emailContact,
        public string $rutLegalAgent,
        public string $fullNameLegalAgent,
        public string $resolutionNumberDte,
        public string $resolutionDateDte,
        public string $resolutionNumberTicket,
        public string $resolutionTicketDate,
        public ?string $idempotencyKey = null
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'businessName' => $this->businessName,
            'rut' => $this->rut,
            'activity' => $this->activity,
            'address' => $this->address,
            'commune' => $this->commune,
            'city' => $this->city,
            'emailDte' => $this->emailDte,
            'emailContact' => $this->emailContact,
            'rutLegalAgent' => $this->rutLegalAgent,
            'fullNameLegalAgent' => $this->fullNameLegalAgent,
            'resolutionNumberDte' => $this->resolutionNumberDte,
            'resolutionDateDte' => $this->resolutionDateDte,
            'resolutionNumberTicket' => $this->resolutionNumberTicket,
            'resolutionTicketDate' => $this->resolutionTicketDate,
        ];
    }
}
