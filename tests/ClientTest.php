<?php

declare(strict_types=1);

namespace IntegraFacturacion\Tests;

use IntegraFacturacion\Adapters\HttpIntegra\ApiError;
use IntegraFacturacion\Adapters\HttpIntegra\Client;
use IntegraFacturacion\Adapters\HttpIntegra\Config;
use IntegraFacturacion\Adapters\HttpIntegra\HttpResponse;
use IntegraFacturacion\Adapters\HttpIntegra\HttpTransportInterface;
use IntegraFacturacion\Domain\CreateDocumentRequest;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    public function testCreateDocumentSendsIdempotencyHeader(): void
    {
        $transport = new class () implements HttpTransportInterface {
            /** @var array<string, string> */
            public array $headers = [];

            public function send(string $method, string $url, array $headers, ?string $body): HttpResponse
            {
                $this->headers = $headers;
                return new HttpResponse(200, '{"ok":true}');
            }
        };

        $client = new Client(new Config(apiKey: 'key', transport: $transport));
        $client->createDocument(new CreateDocumentRequest('33', '{"foo":"bar"}', idempotencyKey: 'idem-1'));

        self::assertArrayHasKey('idempotency-key', $transport->headers);
        self::assertSame('idem-1', $transport->headers['idempotency-key']);
    }

    public function testApiErrorOnNon2xx(): void
    {
        $transport = new class () implements HttpTransportInterface {
            public function send(string $method, string $url, array $headers, ?string $body): HttpResponse
            {
                return new HttpResponse(422, '{"error":"bad"}');
            }
        };

        $client = new Client(new Config(apiKey: 'key', transport: $transport));

        $this->expectException(ApiError::class);
        $client->getDocumentStats();
    }

    public function testGetLastUsedFolioBuildsQuery(): void
    {
        $transport = new class () implements HttpTransportInterface {
            public string $url = '';

            public function send(string $method, string $url, array $headers, ?string $body): HttpResponse
            {
                $this->url = $url;
                return new HttpResponse(200, '{"ok":true}');
            }
        };

        $client = new Client(new Config(apiKey: 'key', baseUrl: 'https://api.integrafacturacion.cl', transport: $transport));
        $client->getLastUsedFolio('33');

        self::assertStringContainsString('code_sii=33', $transport->url);
    }
}
