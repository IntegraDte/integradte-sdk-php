<?php

declare(strict_types=1);

namespace IntegraDte\Domain\Dte;

final class Encabezado33 implements \JsonSerializable
{
    use JsonHelpers;

    public function __construct(public IdDocBase $idDoc, public Emisor $emisor, public Receptor $receptor, public Totales $totales)
    {
    }

    public function jsonSerialize(): array
    {
        return ['IdDoc' => $this->idDoc, 'Emisor' => $this->emisor, 'Receptor' => $this->receptor, 'Totales' => $this->totales];
    }
}

final class Encabezado34 implements \JsonSerializable
{
    use JsonHelpers;

    public function __construct(public IdDocBase $idDoc, public Emisor $emisor, public Receptor $receptor, public Totales $totales)
    {
    }

    public function jsonSerialize(): array
    {
        return ['IdDoc' => $this->idDoc, 'Emisor' => $this->emisor, 'Receptor' => $this->receptor, 'Totales' => $this->totales];
    }
}

final class Encabezado39 implements \JsonSerializable
{
    use JsonHelpers;

    public function __construct(public IdDocBoleta $idDoc, public EmisorBoleta $emisor, public ReceptorBoleta $receptor, public TotalesBoleta $totales)
    {
    }

    public function jsonSerialize(): array
    {
        return ['IdDoc' => $this->idDoc, 'Emisor' => $this->emisor, 'Receptor' => $this->receptor, 'Totales' => $this->totales];
    }
}

final class Encabezado41 implements \JsonSerializable
{
    use JsonHelpers;

    public function __construct(public IdDocBoleta $idDoc, public EmisorBoleta $emisor, public ReceptorBoleta $receptor, public TotalesBoleta $totales)
    {
    }

    public function jsonSerialize(): array
    {
        return ['IdDoc' => $this->idDoc, 'Emisor' => $this->emisor, 'Receptor' => $this->receptor, 'Totales' => $this->totales];
    }
}

final class Encabezado46 implements \JsonSerializable
{
    use JsonHelpers;

    public function __construct(public IdDocBase $idDoc, public Emisor $emisor, public Receptor $receptor, public Totales $totales)
    {
    }

    public function jsonSerialize(): array
    {
        return ['IdDoc' => $this->idDoc, 'Emisor' => $this->emisor, 'Receptor' => $this->receptor, 'Totales' => $this->totales];
    }
}

final class Encabezado52 implements \JsonSerializable
{
    use JsonHelpers;

    public function __construct(public IdDocGuia $idDoc, public Emisor $emisor, public Receptor $receptor, public TotalesGuia $totales)
    {
    }

    public function jsonSerialize(): array
    {
        return ['IdDoc' => $this->idDoc, 'Emisor' => $this->emisor, 'Receptor' => $this->receptor, 'Totales' => $this->totales];
    }
}

final class Encabezado56 implements \JsonSerializable
{
    use JsonHelpers;

    public function __construct(public IdDocBase $idDoc, public Emisor $emisor, public Receptor $receptor, public Totales $totales)
    {
    }

    public function jsonSerialize(): array
    {
        return ['IdDoc' => $this->idDoc, 'Emisor' => $this->emisor, 'Receptor' => $this->receptor, 'Totales' => $this->totales];
    }
}

final class Encabezado61 implements \JsonSerializable
{
    use JsonHelpers;

    public function __construct(public IdDocBase $idDoc, public Emisor $emisor, public Receptor $receptor, public Totales $totales)
    {
    }

    public function jsonSerialize(): array
    {
        return ['IdDoc' => $this->idDoc, 'Emisor' => $this->emisor, 'Receptor' => $this->receptor, 'Totales' => $this->totales];
    }
}

abstract class BaseDteData implements \JsonSerializable
{
    use JsonHelpers;

    /** @param Detalle[] $detalle */
    /** @param DscRcgGlobal[]|null $dscRcgGlobal */
    /** @param Referencia[]|null $referencia */
    /** @param Comisiones[]|null $comisiones */
    /** @param SubTotInfo[]|null $subTotInfo */
    public function __construct(
        protected \JsonSerializable $encabezado,
        protected array $detalle,
        protected ?array $dscRcgGlobal = null,
        protected ?array $referencia = null,
        protected ?array $comisiones = null,
        protected ?array $subTotInfo = null,
        protected ?\JsonSerializable $transporte = null,
        protected ?OtraMoneda $otraMoneda = null
    ) {
    }

    public function jsonSerialize(): array
    {
        return $this->clean([
            'Encabezado' => $this->encabezado,
            'Detalle' => $this->detalle,
            'DscRcgGlobal' => $this->dscRcgGlobal,
            'Referencia' => $this->referencia,
            'Comisiones' => $this->comisiones,
            'SubTotInfo' => $this->subTotInfo,
            'Transporte' => $this->transporte,
            'OtraMoneda' => $this->otraMoneda,
        ]);
    }
}

final class Dte33Data extends BaseDteData {}
final class Dte34Data extends BaseDteData {}
final class Dte39Data extends BaseDteData {}
final class Dte41Data extends BaseDteData {}
final class Dte46Data extends BaseDteData {}
final class Dte52Data extends BaseDteData {}
final class Dte56Data extends BaseDteData {}
final class Dte61Data extends BaseDteData {}
