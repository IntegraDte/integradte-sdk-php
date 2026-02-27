<?php

declare(strict_types=1);

namespace IntegraFacturacion\Domain;

use IntegraFacturacion\Domain\Dte\Dte33Data;
use IntegraFacturacion\Domain\Dte\Dte34Data;
use IntegraFacturacion\Domain\Dte\Dte39Data;
use IntegraFacturacion\Domain\Dte\Dte41Data;
use IntegraFacturacion\Domain\Dte\Dte46Data;
use IntegraFacturacion\Domain\Dte\Dte52Data;
use IntegraFacturacion\Domain\Dte\Dte56Data;
use IntegraFacturacion\Domain\Dte\Dte61Data;
use InvalidArgumentException;
use JsonException;

final class DteBuilder
{
    public static function createDocumentRequestFromString(
        string $codeSii,
        string $dataDte,
        ?string $userId = null,
        ?string $businessId = null,
        ?string $idempotencyKey = null
    ): CreateDocumentRequest {
        if (trim($codeSii) === '') {
            throw new InvalidArgumentException('code_sii is required');
        }

        if (trim($dataDte) === '') {
            throw new InvalidArgumentException('data_dte is required');
        }

        return new CreateDocumentRequest(
            codeSii: $codeSii,
            dataDte: $dataDte,
            userId: $userId,
            businessId: $businessId,
            idempotencyKey: $idempotencyKey
        );
    }

    /**
     * @param array<string, mixed>|object $dte
     */
    public static function createDocumentRequestFromDte(
        string $codeSii,
        ?string $userId,
        ?string $businessId,
        ?string $idempotencyKey,
        array|object $dte
    ): CreateDocumentRequest {
        if (trim($codeSii) === '') {
            throw new InvalidArgumentException('code_sii is required');
        }

        try {
            $dataDte = json_encode($dte, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidArgumentException('Invalid DTE payload: ' . $e->getMessage(), 0, $e);
        }

        return new CreateDocumentRequest(
            codeSii: $codeSii,
            dataDte: $dataDte,
            userId: $userId,
            businessId: $businessId,
            idempotencyKey: $idempotencyKey
        );
    }

    /** @param array<string, mixed>|object $dte */
    public static function dte33ToRequest(?string $userId, ?string $businessId, ?string $idempotencyKey, Dte33Data|array $dte): CreateDocumentRequest
    {
        return self::createDocumentRequestFromDte('33', $userId, $businessId, $idempotencyKey, $dte);
    }

    public static function dte34ToRequest(?string $userId, ?string $businessId, ?string $idempotencyKey, Dte34Data|array $dte): CreateDocumentRequest
    {
        return self::createDocumentRequestFromDte('34', $userId, $businessId, $idempotencyKey, $dte);
    }

    public static function dte39ToRequest(?string $userId, ?string $businessId, ?string $idempotencyKey, Dte39Data|array $dte): CreateDocumentRequest
    {
        return self::createDocumentRequestFromDte('39', $userId, $businessId, $idempotencyKey, $dte);
    }

    public static function dte41ToRequest(?string $userId, ?string $businessId, ?string $idempotencyKey, Dte41Data|array $dte): CreateDocumentRequest
    {
        return self::createDocumentRequestFromDte('41', $userId, $businessId, $idempotencyKey, $dte);
    }

    public static function dte46ToRequest(?string $userId, ?string $businessId, ?string $idempotencyKey, Dte46Data|array $dte): CreateDocumentRequest
    {
        return self::createDocumentRequestFromDte('46', $userId, $businessId, $idempotencyKey, $dte);
    }

    public static function dte52ToRequest(?string $userId, ?string $businessId, ?string $idempotencyKey, Dte52Data|array $dte): CreateDocumentRequest
    {
        return self::createDocumentRequestFromDte('52', $userId, $businessId, $idempotencyKey, $dte);
    }

    public static function dte56ToRequest(?string $userId, ?string $businessId, ?string $idempotencyKey, Dte56Data|array $dte): CreateDocumentRequest
    {
        return self::createDocumentRequestFromDte('56', $userId, $businessId, $idempotencyKey, $dte);
    }

    public static function dte61ToRequest(?string $userId, ?string $businessId, ?string $idempotencyKey, Dte61Data|array $dte): CreateDocumentRequest
    {
        return self::createDocumentRequestFromDte('61', $userId, $businessId, $idempotencyKey, $dte);
    }
}
