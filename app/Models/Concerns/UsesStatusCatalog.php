<?php

namespace App\Models\Concerns;

/**
 * Trait de utilidad para estandarizar el estado lógico `status_id` basado en la tabla maestra `status`.
 *
 * Propósito: Evitar lógica duplicada al convertir filtros (`all`, `'true'`, `'false'`, `1`, `2`)
 * y estados hacia la convención oficial de `sogac_mio.sql` (`status_id = 1` Activo, `2` Inactivo).
 *
 * Historial:
 *  - 2026-11-14: Creación para reemplazar el booleano `status` eliminado por `sogac_mio.sql`.
 */
trait UsesStatusCatalog
{
    public const STATUS_ACTIVO = 1;
    public const STATUS_INACTIVO = 2;

    /**
     * Convierte un filtro recibido desde UI/QueryString al `status_id` entero correspondiente.
     */
    protected static function resolverFiltroStatusId(mixed $status): ?int
    {
        if ($status === null || $status === '' || $status === 'all') {
            return null;
        }

        if ($status === self::STATUS_INACTIVO || $status === (string) self::STATUS_INACTIVO) {
            return self::STATUS_INACTIVO;
        }

        if ($status === self::STATUS_ACTIVO || $status === (string) self::STATUS_ACTIVO) {
            return self::STATUS_ACTIVO;
        }

        $bool = filter_var($status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($bool === null) {
            return null;
        }

        return $bool ? self::STATUS_ACTIVO : self::STATUS_INACTIVO;
    }

    /**
     * Determina si un registro o valor de `status_id` representa estado Activo (`1`).
     */
    protected static function esStatusActivo(mixed $valor): bool
    {
        if (is_object($valor)) {
            return (int) ($valor->status_id ?? self::STATUS_INACTIVO) === self::STATUS_ACTIVO;
        }

        return (int) $valor === self::STATUS_ACTIVO;
    }
}
