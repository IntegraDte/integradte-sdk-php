# integradte-sdk-php

SDK en PHP para consumir la API de [IntegraDte](https://integradte.cl), basado en arquitectura hexagonal.

## Instalacion

```bash
composer require IntegraDte/integradte-sdk-php
```

## Estructura hexagonal

- `src/Domain`: DTOs y builder para `data_dte`
- `src/Ports`: contratos de salida (`IntegraDteApiInterface`, `ExtendedIntegraDteApiInterface` y `FullIntegraDteApiInterface`, cada uno extiende al anterior)
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
    idempotencyKey: '0190f5b4-7c1e-4a3b-9c2d-1e2f3a4b5c6d' // UUID; opcional, si falta el SDK genera uno
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
    idempotencyKey: '0190f5b4-7c1e-4a3b-9c2d-1e2f3a4b5c6d'
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
    idempotencyKey: '0190f5b4-7c1e-4a3b-9c2d-1e2f3a4b5c6d',
    dte: $dte
);
```

## Verificar el certificado de la empresa

`getCertificateInfo` (`GET /api/v1/business/certificate-info`) solo responde si la empresa puede firmar. No devuelve datos del certificado:

```php
<?php

$info = $service->getCertificateInfo();
// ['success' => true, 'message' => '...', 'data' => ['has_valid_certificate' => true]]

if (!$info['data']['has_valid_certificate']) {
    // Sin certificado, no abre con la contraseña guardada o vencido: subir uno con uploadCertificate.
}
```

`has_valid_certificate` es `true` cuando la empresa tiene certificado, este abre con la contraseña guardada y no esta vencido (la misma validacion que usa la emision). Si la empresa no tiene certificado la respuesta es `200` con `false`.

## Endpoints implementados

- `createDocument`
- `getDocuments`
- `getDocument`
- `getDocumentStats`
- `getDocumentStatsWithFilters`
- `createCession`
- `generatePdf`
- `createBusiness`
- `getBusinesses`
- `getBusiness`
- `enableProductionMode`
- `enableCertificationMode`
- `updateBusiness`
- `uploadCertificate`
- `getCertificateInfo`
- `getMe`
- `getBillingBalance`
- `getBillingPayments`
- `createPurchase`
- `getPurchaseAcknowledgments`
- `getNumerationSummary`
- `getLastUsedFolio`
- `uploadNumeration`
- `deleteNumeration`
- `requestNumbers`
- `requeueDocument`
- `requeueDocumentStatus`

Resto de la API publica (puerto `FullIntegraDteApiInterface`, que implementa `Client`):

| Metodo | Ruta | Notas |
|---|---|---|
| `getHealth()` | `GET /api/v1/health` | Sin autenticacion. Devuelve el JSON crudo, sin `success`/`data`. |
| `login(LoginRequest)` | `POST /api/v1/auth/login` | Sin autenticacion. `data.xUserKey` es el `x-user-key`. |
| `createFirstBusiness(CreateFirstBusinessRequest, $userKey)` | `POST /api/v1/onboarding/businesses` | Envia `x-user-key` en vez de `x-api-key`. |
| `updateDocument($id, UpdateDocumentRequest)` | `PUT /api/v1/documents/:id` | `dataDte` (string) o `dataDteJson` (arreglo o string JSON). |
| `updateNumerationNextNumber($numerationId, UpdateNumerationNextNumberRequest)` | `PATCH /api/v1/numerations/:numerationId/next-number` | `$numerationId` es el id del rango CAF (`ranges[].id`). |
| `updateLowStockConfig(UpdateLowStockConfigRequest)` | `PATCH /api/v1/numerations/low-stock` | Lista de `LowStockConfigItem`; mezcla por `code_sii`. |
| `listNumerationRanges($filters)` | `GET /api/v1/numerations/ranges` | Filtro `code_sii`. |
| `requeuePurchase(RequeuePurchaseRequest)` | `POST /api/v1/purchase-acknowledgments/requeue` | Ruta cobrable, con limite de reintentos. |
| `listBillingCharges($filters)` | `GET /api/v1/billing/charges` | `page`, `limit`, `status`, `pricing_key`, `from_date`, `to_date`. |
| `listBillingPlans()` | `GET /api/v1/billing/plans` | `data` es una lista. |
| `listBillingInvoices($filters)` | `GET /api/v1/billing/invoices` | Filtro `status`; `data` es una lista. |
| `previewSubscriptionUpgrade($planId)` | `GET /api/v1/billing/subscription/upgrade/preview` | Acepta el id o el `code` del plan. Solo cotiza. |
| `getConsumption()` | `GET /api/v1/consumption` | Consumo del ciclo actual. |
| `listConsumptionOverages($filters)` | `GET /api/v1/consumption/overages` | `page`, `limit`. |
| `listConsumptionOperations($filters)` | `GET /api/v1/consumption/operations` | `period` (`YYYY-MM`). Sin paginar. |
| `requeueCession(RequeueCessionRequest)` | `POST /api/v1/cessions/requeue` | Ruta cobrable, con limite de reintentos. |
| `listCessions($filters)` | `GET /api/v1/cessions` | `page`, `limit`, `document_id`. La lista viene en `data.cessions`. |
| `getCession($id)` | `GET /api/v1/cessions/:id` | |

## Idempotencia (`idempotency-key`)

Estas rutas **exigen** el header `idempotency-key`: sin el, la API responde `400 "idempotency-key header is required"`. Acepta cualquier version de UUID.

| Metodo | Ruta |
|---|---|
| `createDocument` | `POST /api/v1/documents` |
| `updateDocument` | `PUT /api/v1/documents/:id` |
| `createBusiness` | `POST /api/v1/businesses` |
| `updateBusiness` | `PUT /api/v1/businesses/:id` |
| `uploadCertificate` | `PUT /api/v1/business/:id/certificate` |
| `uploadNumeration` | `PUT /api/v1/numerations` |
| `deleteNumeration` | `DELETE /api/v1/numerations/:id` |
| `updateNumerationNextNumber` | `PATCH /api/v1/numerations/:numerationId/next-number` |
| `updateLowStockConfig` | `PATCH /api/v1/numerations/low-stock` |
| `createPurchase` | `POST /api/v1/purchase-acknowledgments` |
| `createCession` | `POST /api/v1/cessions` |

En ellas el SDK **siempre** envia el header. Si pasas una clave (`idempotencyKey` en el request, o el segundo parametro de `deleteNumeration($id, $idempotencyKey)`), se usa esa. Si no pasas ninguna, o viene vacia, el SDK genera una UUID v4 nueva en cada llamada.

```php
<?php

use IntegraDte\Adapters\HttpIntegra\Client;
use IntegraDte\Domain\CreateDocumentRequest;

// Guardar la clave antes de enviar permite reintentar la misma operacion.
$key = Client::generateIdempotencyKey();

$service->createDocument(new CreateDocumentRequest(
    codeSii: '33',
    dataDte: $dataDte,
    idempotencyKey: $key
));
```

- La clave que pases debe ser un UUID. Otro formato responde `400 "idempotency-key must be a valid UUID"`.
- La API guarda la clave por usuario y ruta durante 24 horas y **no compara el body**: si reusas la clave con otro body, devuelve la primera respuesta.
- Si un intento fallo (por ejemplo un error de validacion) o usas `updateDocument`, reintenta con una clave nueva. La API no guarda esas respuestas y reusar la clave responde `500 "failed to parse cached response"`.
- En las demas rutas el header solo se envia si lo pasas (`generatePdf`, `requeuePurchase`, `requeueCession`).

## Onboarding: login y primera empresa

`login` y `createFirstBusiness` sirven para obtener el primer `x-api-key`. Ninguna de las dos envia el `x-api-key` de `Config`. `Config` sigue exigiendo un `apiKey` no vacio, asi que mientras aun no tienes uno puedes usar cualquier valor provisional.

```php
<?php

use IntegraDte\Adapters\HttpIntegra\Client;
use IntegraDte\Adapters\HttpIntegra\Config;
use IntegraDte\Application\Service;
use IntegraDte\Domain\CreateFirstBusinessRequest;
use IntegraDte\Domain\LoginRequest;

$bootstrap = new Service(new Client(new Config(apiKey: 'pendiente')));

$login = $bootstrap->login(new LoginRequest(email: 'yo@empresa.cl', password: 'secreto'));
$userKey = $login['data']['xUserKey'];

$business = $bootstrap->createFirstBusiness(new CreateFirstBusinessRequest(
    businessName: 'Empresa SpA',
    rut: '76000000-0',
    activity: 'Servicios de desarrollo de software',
    address: 'Av. Apoquindo 3000',
    commune: 'Las Condes',
    emailDte: 'dte@empresa.cl',
    emailContact: 'contacto@empresa.cl',
    rutLegalAgent: '12345678-9',
    fullNameLegalAgent: 'Ana Perez',
    resolutionNumberDte: '0',
    resolutionDateDte: '2014-08-22',
    resolutionNumberTicket: '0',
    resolutionTicketDate: '2014-08-22',
    region: 'Metropolitana' // la API exige region o city
), $userKey);

// x-api-key para operar desde ahora.
$apiKey = $business['data']['apiToken']['xApiKey'];
$service = new Service(new Client(new Config(apiKey: $apiKey)));
```

## Workflows incluidos

- `CI` (`.github/workflows/ci.yml`): valida composer y ejecuta tests.
- `Release Please` (`.github/workflows/release-please.yml`): PR/tag/release automaticos con Conventional Commits.
- `Auto Merge Release PR` (`.github/workflows/auto-merge-release-pr.yml`): habilita automerge para PRs de release.
