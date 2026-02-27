<?php

declare(strict_types=1);

namespace IntegraFacturacion\Domain\Dte;

trait JsonHelpers
{
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
