/**
 * Utilidad compartida de estado lógico para tablas y catálogos (`status_id`).
 *
 * Propósito: Normalizar la evaluación de registros activos/inactivos sobre el catálogo oficial
 * `status` de `sogac_mio.sql` (`1 = Activo`, `2 = Inactivo`) con tolerancia de lectura para
 * alias heredados durante la transición.
 *
 * Historial:
 *  - 2026-11-14: Creación para unificar las comprobaciones de `status_id === 1` en vistas Vue.
 */
export function resolverStatusId(registro: unknown): number {
    if (registro === null || registro === undefined) {
        return 2;
    }

    if (typeof registro === 'object') {
        const obj = registro as Record<string, unknown>;
        if (obj.status_id !== undefined && obj.status_id !== null) {
            return Number(obj.status_id) === 1 ? 1 : 2;
        }
        if (obj.status !== undefined && obj.status !== null) {
            return obj.status === true || obj.status === 1 || obj.status === '1' || obj.status === 'true' ? 1 : 2;
        }
        return 1;
    }

    return registro === true || registro === 1 || registro === '1' || registro === 'true' || registro === 'A' ? 1 : 2;
}

export function esActivo(registro: unknown): boolean {
    return resolverStatusId(registro) === 1;
}
