<?php

declare(strict_types=1);

namespace IntegraDte\Tests;

use IntegraDte\Domain\Dte\Detalle;
use IntegraDte\Domain\Dte\Dte33Data;
use IntegraDte\Domain\Dte\Emisor;
use IntegraDte\Domain\Dte\Encabezado33;
use IntegraDte\Domain\Dte\IdDocBase;
use IntegraDte\Domain\Dte\Receptor;
use IntegraDte\Domain\Dte\Totales;
use IntegraDte\Domain\DteBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class DteBuilderTest extends TestCase
{
    public function testBuildsDte33Request(): void
    {
        $request = DteBuilder::dte33ToRequest('user', 'business', 'idem', [
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' => 33,
                    'FchEmis' => '2026-02-03',
                ],
            ],
        ]);

        self::assertSame('33', $request->codeSii);
        self::assertSame('user', $request->userId);
        self::assertSame('business', $request->businessId);
        self::assertSame('idem', $request->idempotencyKey);
        self::assertStringContainsString('Encabezado', $request->dataDte);
    }

    public function testCodeSiiIsRequired(): void
    {
        $this->expectException(InvalidArgumentException::class);
        DteBuilder::createDocumentRequestFromDte('', null, null, null, []);
    }

    public function testBuildFromString(): void
    {
        $request = DteBuilder::createDocumentRequestFromString(
            codeSii: '33',
            dataDte: '{"foo":"bar"}',
            userId: 'u1',
            businessId: 'b1',
            idempotencyKey: 'i1'
        );

        self::assertSame('33', $request->codeSii);
        self::assertSame('{"foo":"bar"}', $request->dataDte);
        self::assertSame('u1', $request->userId);
    }

    public function testBuildFromTypedStruct(): void
    {
        $dte = new Dte33Data(
            encabezado: new Encabezado33(
                idDoc: new IdDocBase(tipoDte: 33, fchEmis: '2026-02-03'),
                emisor: new Emisor(
                    rutEmisor: '12345689-3',
                    rznSoc: 'EMPRESA DE PRUEBA',
                    giroEmis: 'Servicios',
                    dirOrigen: 'Calle 1',
                    cmnaOrigen: 'Santiago'
                ),
                receptor: new Receptor(rutRecep: '12236547-6', rznSocRecep: 'Cliente'),
                totales: new Totales(mntTotal: 119000, mntNeto: 100000, iva: 19000)
            ),
            detalle: [new Detalle(nroLinDet: 1, nmbItem: 'Servicio', montoItem: 100000)]
        );

        $request = DteBuilder::dte33ToRequest('u', 'b', 'i', $dte);
        self::assertStringContainsString('"TipoDTE":33', $request->dataDte);
        self::assertStringContainsString('"NmbItem":"Servicio"', $request->dataDte);
    }
}
