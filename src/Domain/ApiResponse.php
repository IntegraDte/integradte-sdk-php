<?php

declare(strict_types=1);

namespace IntegraDte\Domain;

/**
 * @phpstan-type ApiResponse array<string, mixed>
 */
final class ApiResponse
{
    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function fromArray(array $data): array
    {
        return $data;
    }
}
