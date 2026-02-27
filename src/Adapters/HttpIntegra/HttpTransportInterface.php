<?php

declare(strict_types=1);

namespace IntegraFacturacion\Adapters\HttpIntegra;

interface HttpTransportInterface
{
    /**
     * @param array<string, string> $headers
     */
    public function send(string $method, string $url, array $headers, ?string $body): HttpResponse;
}
