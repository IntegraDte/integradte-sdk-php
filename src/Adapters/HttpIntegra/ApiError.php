<?php

declare(strict_types=1);

namespace IntegraFacturacion\Adapters\HttpIntegra;

use RuntimeException;

final class ApiError extends RuntimeException
{
    public function __construct(public readonly int $statusCode, public readonly string $body)
    {
        parent::__construct(sprintf('integrafacturacion: status=%d body=%s', $statusCode, $body));
    }
}
