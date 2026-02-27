<?php

declare(strict_types=1);

namespace IntegraFacturacion\Adapters\HttpIntegra;

use RuntimeException;

final class CurlTransport implements HttpTransportInterface
{
    public function __construct(private readonly int $timeoutSeconds)
    {
    }

    public function send(string $method, string $url, array $headers, ?string $body): HttpResponse
    {
        $curl = curl_init($url);
        if ($curl === false) {
            throw new RuntimeException('integrafacturacion: unable to initialize curl');
        }

        $headerLines = [];
        foreach ($headers as $key => $value) {
            $headerLines[] = $key . ': ' . $value;
        }

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headerLines,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
        ]);

        if ($body !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }

        $rawBody = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($rawBody === false) {
            $error = curl_error($curl);
            // CurlHandle is released automatically; explicit close is deprecated.
            $curl = null;
            throw new RuntimeException('integrafacturacion: transport error: ' . $error);
        }
        $curl = null;

        return new HttpResponse($statusCode, (string) $rawBody);
    }
}
