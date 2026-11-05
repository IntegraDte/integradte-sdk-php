<?php

declare(strict_types=1);

namespace IntegraDte\Domain\Dte;

trait JsonHelpers
{
    /**
     * bloqueChofer devuelve el bloque Chofer que exige el esquema del SII, armandolo desde los
     * campos planos si hace falta.
     *
     * Devuelve el objeto y no un array a proposito: clean() convierte cualquier array en lista
     * y perderia las claves.
     */
    protected function bloqueChofer(?Chofer $chofer, ?string $rutChofer, ?string $nombreChofer): ?Chofer
    {
        if ($chofer !== null) {
            return $chofer;
        }

        if ($rutChofer === null && $nombreChofer === null) {
            return null;
        }

        return new Chofer($rutChofer, $nombreChofer);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    protected function clean(array $payload): array
    {
        $out = [];
        foreach ($payload as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_array($value)) {
                $mapped = [];
                foreach ($value as $item) {
                    if ($item instanceof \JsonSerializable) {
                        $mapped[] = $item->jsonSerialize();
                    } else {
                        $mapped[] = $item;
                    }
                }
                $out[$key] = $mapped;
                continue;
            }

            if ($value instanceof \JsonSerializable) {
                $out[$key] = $value->jsonSerialize();
                continue;
            }

            $out[$key] = $value;
        }

        return $out;
    }
}
