<?php

declare(strict_types=1);

namespace IntegraDte\Domain\Dte;

final class Emisor implements \JsonSerializable
{
    use JsonHelpers;

    /** @param int[]|null $acteco */
    public function __construct(
        public string $rutEmisor,
        public string $rznSoc,
        public string $giroEmis,
        public string $dirOrigen,
        public string $cmnaOrigen,
        public ?string $telefono = null,
        public ?string $correoEmisor = null,
        public ?array $acteco = null,
        public ?int $cdgSiiSucur = null,
        public ?string $ciudadOrigen = null
    ) {
    }

    public function jsonSerialize(): array
    {
        return $this->clean([
            'RUTEmisor' => $this->rutEmisor,
            'RznSoc' => $this->rznSoc,
            'GiroEmis' => $this->giroEmis,
            'Telefono' => $this->telefono,
            'CorreoEmisor' => $this->correoEmisor,
            'Acteco' => $this->acteco,
            'CdgSIISucur' => $this->cdgSiiSucur,
            'DirOrigen' => $this->dirOrigen,
            'CmnaOrigen' => $this->cmnaOrigen,
            'CiudadOrigen' => $this->ciudadOrigen,
        ]);
    }
}

final class EmisorBoleta implements \JsonSerializable
{
    use JsonHelpers;

    public function __construct(
        public string $rutEmisor,
        public string $rznSocEmisor,
        public string $giroEmisor,
        public string $dirOrigen,
        public string $cmnaOrigen,
        public ?string $telefono = null,
        public ?string $correoEmisor = null,
        public ?int $cdgSiiSucur = null,
        public ?string $ciudadOrigen = null
    ) {
    }

    public function jsonSerialize(): array
    {
        return $this->clean([
            'RUTEmisor' => $this->rutEmisor,
            'RznSocEmisor' => $this->rznSocEmisor,
            'GiroEmisor' => $this->giroEmisor,
            'Telefono' => $this->telefono,
            'CorreoEmisor' => $this->correoEmisor,
            'CdgSIISucur' => $this->cdgSiiSucur,
            'DirOrigen' => $this->dirOrigen,
            'CmnaOrigen' => $this->cmnaOrigen,
            'CiudadOrigen' => $this->ciudadOrigen,
        ]);
    }
}

final class Receptor implements \JsonSerializable
{
    use JsonHelpers;

    public function __construct(
        public string $rutRecep,
        public string $rznSocRecep,
        public ?string $cdgIntRecep = null,
        public ?string $giroRecep = null,
        public ?string $contacto = null,
        public ?string $correoRecep = null,
        public ?string $dirRecep = null,
        public ?string $cmnaRecep = null,
        public ?string $ciudadRecep = null,
        public ?string $dirPostal = null,
        public ?string $cmnaPostal = null,
        public ?string $ciudadPostal = null
    ) {
    }

    public function jsonSerialize(): array
    {
        return $this->clean([
            'RUTRecep' => $this->rutRecep,
            'CdgIntRecep' => $this->cdgIntRecep,
            'RznSocRecep' => $this->rznSocRecep,
            'GiroRecep' => $this->giroRecep,
            'Contacto' => $this->contacto,
            'CorreoRecep' => $this->correoRecep,
            'DirRecep' => $this->dirRecep,
            'CmnaRecep' => $this->cmnaRecep,
            'CiudadRecep' => $this->ciudadRecep,
            'DirPostal' => $this->dirPostal,
            'CmnaPostal' => $this->cmnaPostal,
            'CiudadPostal' => $this->ciudadPostal,
        ]);
    }
}

final class ReceptorBoleta implements \JsonSerializable
{
    use JsonHelpers;

    public function __construct(
        public ?string $rutRecep = null,
        public ?string $rznSocRecep = null,
        public ?string $giroRecep = null,
        public ?string $contacto = null,
        public ?string $correoRecep = null,
        public ?string $dirRecep = null,
        public ?string $cmnaRecep = null,
        public ?string $ciudadRecep = null
    ) {
    }

    public function jsonSerialize(): array
    {
        return $this->clean([
            'RUTRecep' => $this->rutRecep,
            'RznSocRecep' => $this->rznSocRecep,
            'GiroRecep' => $this->giroRecep,
            'Contacto' => $this->contacto,
            'CorreoRecep' => $this->correoRecep,
            'DirRecep' => $this->dirRecep,
            'CmnaRecep' => $this->cmnaRecep,
            'CiudadRecep' => $this->ciudadRecep,
        ]);
    }
}

final class ImptoReten implements \JsonSerializable
{
    use JsonHelpers;

    public function __construct(public ?string $tipoImp = null, public ?float $tasaImp = null, public ?int $montoImp = null)
    {
    }

    public function jsonSerialize(): array
    {
        return $this->clean(['TipoImp' => $this->tipoImp, 'TasaImp' => $this->tasaImp, 'MontoImp' => $this->montoImp]);
    }
}

final class Totales implements \JsonSerializable
{
    use JsonHelpers;

    /** @param ImptoReten[]|null $imptoReten */
    public function __construct(
        public int $mntTotal,
        public ?int $mntNeto = null,
        public ?int $mntExe = null,
        public ?float $tasaIva = null,
        public ?int $iva = null,
        public ?int $ivaProp = null,
        public ?int $ivaNoRet = null,
        public ?array $imptoReten = null,
        public ?int $montoNf = null,
        public ?int $totalPeriodo = null,
        public ?int $saldoAnterior = null,
        public ?int $vlrPagar = null
    ) {
    }

    public function jsonSerialize(): array
    {
        return $this->clean([
            'MntNeto' => $this->mntNeto,
            'MntExe' => $this->mntExe,
            'TasaIVA' => $this->tasaIva,
            'IVA' => $this->iva,
            'IVAProp' => $this->ivaProp,
            'IVANoRet' => $this->ivaNoRet,
            'ImptoReten' => $this->imptoReten,
            'MntTotal' => $this->mntTotal,
            'MontoNF' => $this->montoNf,
            'TotalPeriodo' => $this->totalPeriodo,
            'SaldoAnterior' => $this->saldoAnterior,
            'VlrPagar' => $this->vlrPagar,
        ]);
    }
}

final class TotalesBoleta implements \JsonSerializable
{
    use JsonHelpers;

    /** @param ImptoReten[]|null $imptoReten */
    public function __construct(
        public int $mntTotal,
        public ?int $mntNeto = null,
        public ?int $mntExe = null,
        public ?int $iva = null,
        public ?array $imptoReten = null
    ) {
    }

    public function jsonSerialize(): array
    {
        return $this->clean([
            'MntNeto' => $this->mntNeto,
            'MntExe' => $this->mntExe,
            'IVA' => $this->iva,
            'ImptoReten' => $this->imptoReten,
            'MntTotal' => $this->mntTotal,
        ]);
    }
}

final class TotalesGuia implements \JsonSerializable
{
    use JsonHelpers;

    /** @param ImptoReten[]|null $imptoReten */
    public function __construct(
        public ?int $mntTotal = null,
        public ?int $mntNeto = null,
        public ?int $mntExe = null,
        public ?float $tasaIva = null,
        public ?int $iva = null,
        public ?array $imptoReten = null
    ) {
    }

    public function jsonSerialize(): array
    {
        return $this->clean([
            'MntNeto' => $this->mntNeto,
            'MntExe' => $this->mntExe,
            'TasaIVA' => $this->tasaIva,
            'IVA' => $this->iva,
            'ImptoReten' => $this->imptoReten,
            'MntTotal' => $this->mntTotal,
        ]);
    }
}

final class IdDocBase implements \JsonSerializable
{
    use JsonHelpers;

    public function __construct(
        public int $tipoDte,
        public string $fchEmis,
        public int $folio = 0,
        public ?int $indServicio = null,
        public ?int $fmaPago = null,
        public ?string $periodoDesde = null,
        public ?string $periodoHasta = null,
        public ?string $termPagoGlosa = null,
        public ?string $fchVenc = null,
        public ?string $medioPago = null,
        public ?string $tpoCtaPago = null,
        public ?string $numCtaPago = null,
        public ?int $mntBruto = null,
        public ?int $indMntNeto = null
    ) {
    }

    public function jsonSerialize(): array
    {
        return $this->clean([
            'TipoDTE' => $this->tipoDte,
            'Folio' => $this->folio,
            'FchEmis' => $this->fchEmis,
            'IndServicio' => $this->indServicio,
            'FmaPago' => $this->fmaPago,
            'PeriodoDesde' => $this->periodoDesde,
            'PeriodoHasta' => $this->periodoHasta,
            'TermPagoGlosa' => $this->termPagoGlosa,
            'FchVenc' => $this->fchVenc,
            'MedioPago' => $this->medioPago,
            'TpoCtaPago' => $this->tpoCtaPago,
            'NumCtaPago' => $this->numCtaPago,
            'MntBruto' => $this->mntBruto,
            'IndMntNeto' => $this->indMntNeto,
        ]);
    }
}

final class IdDocBoleta implements \JsonSerializable
{
    use JsonHelpers;

    public function __construct(
        public int $tipoDte,
        public string $fchEmis,
        public int $folio = 0,
        public ?int $indServicio = null,
        public ?int $fmaPago = null,
        public ?string $medioPago = null,
        public ?int $mntBruto = null,
        public ?int $indMntNeto = null
    ) {
    }

    public function jsonSerialize(): array
    {
        return $this->clean([
            'TipoDTE' => $this->tipoDte,
            'Folio' => $this->folio,
            'FchEmis' => $this->fchEmis,
            'IndServicio' => $this->indServicio,
            'FmaPago' => $this->fmaPago,
            'MedioPago' => $this->medioPago,
            'MntBruto' => $this->mntBruto,
            'IndMntNeto' => $this->indMntNeto,
        ]);
    }
}

final class IdDocGuia implements \JsonSerializable
{
    use JsonHelpers;

    public function __construct(
        public int $tipoDte,
        public string $fchEmis,
        public int $folio = 0,
        public ?int $tipoDespacho = null,
        public ?int $indTraslado = null,
        public ?int $indServicio = null,
        public ?int $fmaPago = null,
        public ?string $fchVenc = null,
        public ?string $periodoDesde = null,
        public ?string $periodoHasta = null,
        public ?string $termPagoGlosa = null,
        public ?int $mntBruto = null,
        public ?int $indMntNeto = null
    ) {
    }

    public function jsonSerialize(): array
    {
        return $this->clean([
            'TipoDTE' => $this->tipoDte,
            'Folio' => $this->folio,
            'FchEmis' => $this->fchEmis,
            'TipoDespacho' => $this->tipoDespacho,
            'IndTraslado' => $this->indTraslado,
            'IndServicio' => $this->indServicio,
            'FmaPago' => $this->fmaPago,
            'FchVenc' => $this->fchVenc,
            'PeriodoDesde' => $this->periodoDesde,
            'PeriodoHasta' => $this->periodoHasta,
            'TermPagoGlosa' => $this->termPagoGlosa,
            'MntBruto' => $this->mntBruto,
            'IndMntNeto' => $this->indMntNeto,
        ]);
    }
}

final class CdgItem implements \JsonSerializable
{
    use JsonHelpers;

    public function __construct(public ?string $tpoCodigo = null, public ?string $vlrCodigo = null)
    {
    }

    public function jsonSerialize(): array
    {
        return $this->clean(['TpoCodigo' => $this->tpoCodigo, 'VlrCodigo' => $this->vlrCodigo]);
    }
}

final class Detalle implements \JsonSerializable
{
    use JsonHelpers;

    /** @param CdgItem[]|null $cdgItem */
    /** @param string[]|null $codImpAdic */
    public function __construct(
        public int $nroLinDet,
        public string $nmbItem,
        public int $montoItem,
        public ?array $cdgItem = null,
        public ?int $indExe = null,
        public ?string $dscItem = null,
        public ?float $qtyItem = null,
        public ?string $unmdItem = null,
        public ?float $prcItem = null,
        public ?float $descuentoPct = null,
        public ?int $descuentoMonto = null,
        public ?float $recargoPct = null,
        public ?int $recargoMonto = null,
        public ?array $codImpAdic = null
    ) {
    }

    public function jsonSerialize(): array
    {
        return $this->clean([
            'NroLinDet' => $this->nroLinDet,
            'CdgItem' => $this->cdgItem,
            'IndExe' => $this->indExe,
            'NmbItem' => $this->nmbItem,
            'DscItem' => $this->dscItem,
            'QtyItem' => $this->qtyItem,
            'UnmdItem' => $this->unmdItem,
            'PrcItem' => $this->prcItem,
            'DescuentoPct' => $this->descuentoPct,
            'DescuentoMonto' => $this->descuentoMonto,
            'RecargoPct' => $this->recargoPct,
            'RecargoMonto' => $this->recargoMonto,
            'CodImpAdic' => $this->codImpAdic,
            'MontoItem' => $this->montoItem,
        ]);
    }
}

final class DscRcgGlobal implements \JsonSerializable
{
    use JsonHelpers;

    public function __construct(
        public int $nroLinDr,
        public string $tpoMov,
        public string $tpoValor,
        public float $valorDr,
        public ?string $glosaDr = null,
        public ?int $indExeDr = null
    ) {
    }

    public function jsonSerialize(): array
    {
        return $this->clean([
            'NroLinDR' => $this->nroLinDr,
            'TpoMov' => $this->tpoMov,
            'GlosaDR' => $this->glosaDr,
            'TpoValor' => $this->tpoValor,
            'ValorDR' => $this->valorDr,
            'IndExeDR' => $this->indExeDr,
        ]);
    }
}

final class Referencia implements \JsonSerializable
{
    use JsonHelpers;

    public function __construct(
        public ?int $nroLinRef = null,
        public ?string $tpoDocRef = null,
        public ?string $folioRef = null,
        public ?string $fchRef = null,
        public ?int $codRef = null,
        public ?string $razonRef = null
    ) {
    }

    public function jsonSerialize(): array
    {
        return $this->clean([
            'NroLinRef' => $this->nroLinRef,
            'TpoDocRef' => $this->tpoDocRef,
            'FolioRef' => $this->folioRef,
            'FchRef' => $this->fchRef,
            'CodRef' => $this->codRef,
            'RazonRef' => $this->razonRef,
        ]);
    }
}

/**
 * Chofer identifica a quien conduce. El esquema del SII lo exige como bloque propio dentro de
 * Transporte, no como campos planos.
 */
final class Chofer implements \JsonSerializable
{
    use JsonHelpers;

    public function __construct(
        public ?string $rutChofer = null,
        public ?string $nombreChofer = null
    ) {
    }

    public function jsonSerialize(): array
    {
        return $this->clean([
            'RUTChofer' => $this->rutChofer,
            'NombreChofer' => $this->nombreChofer,
        ]);
    }
}

/**
 * Transporte es el bloque de transporte del documento, que va DENTRO de Encabezado, entre
 * Receptor y Totales.
 *
 * PatenteCarro, FchSalida, HraSalida y FchLlegada los agrego el Formato DTE v2.5 (2026-02) y
 * la Res. Ex. SII N 144 de 2026 los volvio obligatorios para los documentos que amparan
 * traslado de bienes. Aca son opcionales porque la API todavia no los exige.
 *
 * El SII valida los formatos al caracter: FchSalida y FchLlegada en AAAA-MM-DD, y HraSalida en
 * HH:MM:SS con segundos y ceros a la izquierda. Tanto "08:30" como "8:30:00" se rechazan.
 *
 * Los parametros nuevos van al final de la firma para no romper a quien construya por posicion.
 * El orden del payload lo decide jsonSerialize(), que emite la secuencia del esquema.
 */
final class Transporte implements \JsonSerializable
{
    use JsonHelpers;

    /**
     * @param string|null $nombreChofer Deprecated: usar $chofer.
     * @param string|null $rutChofer    Deprecated: usar $chofer.
     * @param string|null $nroLicencia  Deprecated: el SII no lo declara. No se serializa.
     */
    public function __construct(
        public ?string $patente = null,
        public ?string $rutTrans = null,
        public ?string $nombreChofer = null,
        public ?string $dirDest = null,
        public ?string $cmnaDest = null,
        public ?string $patenteCarro = null,
        public ?Chofer $chofer = null,
        public ?string $ciudadDest = null,
        public ?string $fchSalida = null,
        public ?string $hraSalida = null,
        public ?string $fchLlegada = null,
        public ?string $rutChofer = null,
        public ?string $nroLicencia = null
    ) {
    }

    public function jsonSerialize(): array
    {
        return $this->clean([
            'Patente' => $this->patente,
            'PatenteCarro' => $this->patenteCarro,
            'RUTTrans' => $this->rutTrans,
            'Chofer' => $this->bloqueChofer($this->chofer, $this->rutChofer, $this->nombreChofer),
            'DirDest' => $this->dirDest,
            'CmnaDest' => $this->cmnaDest,
            'CiudadDest' => $this->ciudadDest,
            'FchSalida' => $this->fchSalida,
            'HraSalida' => $this->hraSalida,
            'FchLlegada' => $this->fchLlegada,
        ]);
    }
}

/**
 * TransporteGuia era el bloque propio de la guia (52). El esquema del SII define un unico
 * Transporte igual para todos los tipos, asi que esta clase emite exactamente lo mismo.
 *
 * Deprecated: usar Transporte.
 */
final class TransporteGuia implements \JsonSerializable
{
    use JsonHelpers;

    /**
     * @param string|null $nombreChofer Deprecated: usar $chofer.
     * @param string|null $rutChofer    Deprecated: usar $chofer.
     * @param string|null $nroLicencia  Deprecated: el SII no lo declara. No se serializa.
     */
    public function __construct(
        public ?string $patente = null,
        public ?string $rutTrans = null,
        public ?string $nombreChofer = null,
        public ?string $rutChofer = null,
        public ?string $nroLicencia = null,
        public ?string $dirDest = null,
        public ?string $cmnaDest = null,
        public ?string $ciudadDest = null,
        public ?string $patenteCarro = null,
        public ?Chofer $chofer = null,
        public ?string $fchSalida = null,
        public ?string $hraSalida = null,
        public ?string $fchLlegada = null
    ) {
    }

    public function jsonSerialize(): array
    {
        return $this->clean([
            'Patente' => $this->patente,
            'PatenteCarro' => $this->patenteCarro,
            'RUTTrans' => $this->rutTrans,
            'Chofer' => $this->bloqueChofer($this->chofer, $this->rutChofer, $this->nombreChofer),
            'DirDest' => $this->dirDest,
            'CmnaDest' => $this->cmnaDest,
            'CiudadDest' => $this->ciudadDest,
            'FchSalida' => $this->fchSalida,
            'HraSalida' => $this->hraSalida,
            'FchLlegada' => $this->fchLlegada,
        ]);
    }
}

final class SubTotInfo implements \JsonSerializable
{
    use JsonHelpers;

    public function __construct(
        public ?int $nroSti = null,
        public ?string $glosaSti = null,
        public ?int $ordenSti = null,
        public ?int $subTotNeto = null,
        public ?int $subTotExe = null,
        public ?int $subTotIva = null,
        public ?int $subTotAdic = null,
        public ?int $subTotTotal = null
    ) {
    }

    public function jsonSerialize(): array
    {
        return $this->clean([
            'NroSTI' => $this->nroSti,
            'GlosaSTI' => $this->glosaSti,
            'OrdenSTI' => $this->ordenSti,
            'SubTotNeto' => $this->subTotNeto,
            'SubTotExe' => $this->subTotExe,
            'SubTotIVA' => $this->subTotIva,
            'SubTotAdic' => $this->subTotAdic,
            'SubTotTotal' => $this->subTotTotal,
        ]);
    }
}

final class Comisiones implements \JsonSerializable
{
    use JsonHelpers;

    public function __construct(
        public ?int $nroLinCom = null,
        public ?string $tipoMovim = null,
        public ?string $glosa = null,
        public ?float $tasaComision = null,
        public ?int $valComision = null
    ) {
    }

    public function jsonSerialize(): array
    {
        return $this->clean([
            'NroLinCom' => $this->nroLinCom,
            'TipoMovim' => $this->tipoMovim,
            'Glosa' => $this->glosa,
            'TasaComision' => $this->tasaComision,
            'ValComision' => $this->valComision,
        ]);
    }
}

final class OtraMoneda implements \JsonSerializable
{
    use JsonHelpers;

    public function __construct(
        public ?string $tpoMoneda = null,
        public ?float $tpoCambio = null,
        public ?int $mntNetoOtrMnd = null,
        public ?int $mntExeOtrMnd = null,
        public ?int $ivaOtrMnd = null,
        public ?int $mntTotOtrMnd = null
    ) {
    }

    public function jsonSerialize(): array
    {
        return $this->clean([
            'TpoMoneda' => $this->tpoMoneda,
            'TpoCambio' => $this->tpoCambio,
            'MntNetoOtrMnd' => $this->mntNetoOtrMnd,
            'MntExeOtrMnd' => $this->mntExeOtrMnd,
            'IVAOtrMnd' => $this->ivaOtrMnd,
            'MntTotOtrMnd' => $this->mntTotOtrMnd,
        ]);
    }
}
