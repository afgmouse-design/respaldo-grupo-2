<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

trait RegistraEnBitacora
{
    public static function bootRegistraEnBitacora(): void
    {
        // Se dispara después de Model::create()
        static::created(function (Model $model) {
            $model->auditarAccion('CREAR', null, $model->getAttributes());
        });

        // Se dispara después de $model->update()
        static::updated(function (Model $model) {
            $cambios = $model->getChanges();

            // Optimización: ignora si el único cambio fue updated_at (sin datos reales)
            if (count($cambios) === 1 && isset($cambios['updated_at'])) {
                return;
            }

            // Solo registra los campos que cambiaron (before / after)
            $original = array_intersect_key($model->getOriginal(), $cambios);

            // Detecta si es un login por el campo 'ultimo_login'
            $accion = !empty(array_intersect(array_keys($cambios), ['ultimo_login']))
                ? 'LOGIN'
                : 'ACTUALIZAR';

            $model->auditarAccion($accion, $original, $cambios);
        });

        // Se dispara después de $model->delete()
        static::deleted(function (Model $model) {
            $model->auditarAccion('ELIMINAR', $model->getOriginal(), null);
        });
    }

    protected function auditarAccion(string $accion, ?array $datosAnteriores, ?array $datosModificados): void
    {
        try {
            // Lee $moduloAuditoria del modelo, o usa el nombre de la clase si no está definido
            $modulo = property_exists($this, 'moduloAuditoria')
                ? $this->moduloAuditoria
                : class_basename(static::class);

            if (class_exists(\App\Services\BitacoraService::class)) {
                app(\App\Services\BitacoraService::class)->registrar(
                    $modulo,
                    $accion,
                    $this->getTable(),
                    $this->getKey(),
                    $datosAnteriores,
                    $datosModificados
                );
            }
        } catch (\Throwable $e) {
            // Un fallo en la bitácora NUNCA debe romper la operación del usuario
            logger()->error('Error en trait RegistraEnBitacora: ' . $e->getMessage());
        }
    }
}
