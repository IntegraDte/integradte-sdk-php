# integradte-sdk-php

SDK en PHP para consumir la API de [IntegraDte](https://integradte.cl), basado en arquitectura hexagonal.

## Instalacion

```bash
composer require joseluis21/integradte-sdk-php
```

## Estructura hexagonal

- `src/Domain`: DTOs y builder para `data_dte`
- `src/Ports`: contrato de salida (`IntegraDteApiInterface`)
- `src/Application`: capa de servicio/casos de uso
- `src/Adapters/HttpIntegra`: adapter HTTP para la API

## Uso recomendado

```php
<?php

require 'vendor/autoload.php';

use IntegraDte\Adapters\HttpIntegra\Client;
use IntegraDte\Adapters\HttpIntegra\Config;
use IntegraDte\Application\Service;
use IntegraDte\Domain\CreateDocumentRequest;

$adapter = new Client(new Config(
    apiKey: 'TU_X_API_KEY'
    // baseUrl: 'https://api.integradte.cl' // opcional
));

$service = new Service($adapter);

$dataDte = Client::encodeDataDte([
    'Encabezado' => [
        'IdDoc' => [
            'TipoDTE' => 33,
            'FchEmis' => '2026-02-03',
        ],
    ],
]);

$response = $service->createDocument(new CreateDocumentRequest(
    codeSii: '33',
    dataDte: $dataDte,
    idempotencyKey: 'mi-idempotency-key-1'
));

var_dump($response);
```

## Crear `data_dte` desde string

```php
<?php

use IntegraDte\Domain\DteBuilder;

$request = DteBuilder::createDocumentRequestFromString(
    codeSii: '33',
    dataDte: '{"Encabezado":{"IdDoc":{"TipoDTE":33,"FchEmis":"2026-02-03"}}}',
    userId: 'user_id',
    businessId: 'business_id',
    idempotencyKey: 'idem-key'
);
```

## Crear `data_dte` desde structs tipadas (como SDK Go)

```php
<?php

use IntegraDte\Domain\Dte\Detalle;
use IntegraDte\Domain\Dte\Dte33Data;
use IntegraDte\Domain\Dte\Emisor;
use IntegraDte\Domain\Dte\Encabezado33;
use IntegraDte\Domain\Dte\IdDocBase;
use IntegraDte\Domain\Dte\Receptor;
use IntegraDte\Domain\Dte\Totales;
use IntegraDte\Domain\DteBuilder;

$dte = new Dte33Data(
    encabezado: new Encabezado33(
        idDoc: new IdDocBase(tipoDte: 33, fchEmis: '2026-02-03'),
        emisor: new Emisor(
            rutEmisor: '12345689-3',
            rznSoc: 'EMPRESA DE PRUEBA',
            giroEmis: 'Servicios de desarrollo de software',
            dirOrigen: 'Av. Apoquindo 3000',
            cmnaOrigen: 'Las Condes'
        ),
        receptor: new Receptor(rutRecep: '12236547-6', rznSocRecep: 'Cliente de Prueba Ltda'),
        totales: new Totales(mntTotal: 119000, mntNeto: 100000, iva: 19000)
    ),
    detalle: [
        new Detalle(nroLinDet: 1, nmbItem: 'Servicio', montoItem: 100000),
    ]
);

$request = DteBuilder::dte33ToRequest(
    userId: 'user_id',
    businessId: 'business_id',
    idempotencyKey: 'idem-key',
    dte: $dte
);
```

## Endpoints implementados

- `createDocument`
- `getDocument`
- `getDocumentStats`
- `createCession`
- `generatePdf`
- `createBusiness`
- `updateBusiness`
- `uploadCertificate`
- `getCertificateInfo`
- `getMe`
- `createPurchase`
- `getNumerationSummary`
- `getLastUsedFolio`
- `uploadNumeration`
- `deleteNumeration`

## Workflows incluidos

- `CI` (`.github/workflows/ci.yml`): valida composer y ejecuta tests.
- `Release Please` (`.github/workflows/release-please.yml`): PR/tag/release automaticos con Conventional Commits.
- `Auto Merge Release PR` (`.github/workflows/auto-merge-release-pr.yml`): habilita automerge para PRs de release.
