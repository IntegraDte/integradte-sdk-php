<?php

declare(strict_types=1);

namespace IntegraDte\Adapters\HttpIntegra;

final class Config
{
    public const DEFAULT_BASE_URL = 'https://api.integradte.cl';

    public function __construct(
        public string $apiKey,
        public string $baseUrl = self::DEFAULT_BASE_URL,
        public string $userAgent = 'integradte-sdk-php/0.1.0',
        public int $timeoutSeconds = 30,
        public ?HttpTransportInterface $transport = null
    ) {
    }
}
