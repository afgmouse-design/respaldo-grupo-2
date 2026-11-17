<?php

namespace App\Providers;

use App\Models\Personas\Persona;
use App\Policies\PersonaPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Persona::class, PersonaPolicy::class);
        Gate::policy(\App\Models\Personas\Perfil::class, \App\Policies\PerfilPolicy::class);
        Gate::policy(\App\Models\OrganizacionInstitucional\Maestros\Universidad::class, \App\Policies\OrganizacionInstitucional\Maestros\UniversidadPolicy::class);
        Gate::policy(\App\Models\OrganizacionInstitucional\Maestros\Sede::class, \App\Policies\OrganizacionInstitucional\Maestros\SedePolicy::class);
        Gate::policy(\App\Models\OrganizacionInstitucional\Maestros\CondicionSede::class, \App\Policies\OrganizacionInstitucional\Maestros\CondicionSedePolicy::class);
        Gate::policy(\App\Models\EstructuraAcademica\Maestros\EscalaMedicion::class, \App\Policies\EstructuraAcademica\Maestros\EscalaMedicionPolicy::class);
        Gate::policy(\App\Models\EstructuraAcademica\Maestros\Programa::class, \App\Policies\EstructuraAcademica\Maestros\ProgramaPolicy::class);
        Gate::policy(\App\Models\EstructuraAcademica\Maestros\Trayecto::class, \App\Policies\EstructuraAcademica\Maestros\TrayectoPolicy::class);
        Gate::policy(\App\Models\ProgramacionAcademica\Maestros\Cohorte::class, \App\Policies\ProgramacionAcademica\Maestros\CohortePolicy::class);
        Gate::policy(\App\Models\ProgramacionAcademica\Maestros\CohorteSede::class, \App\Policies\ProgramacionAcademica\Maestros\CohorteSedePolicy::class);
        Gate::policy(\App\Models\ProgramacionAcademica\Maestros\SedePrograma::class, \App\Policies\ProgramacionAcademica\Maestros\SedeProgramaPolicy::class);
        Gate::policy(\App\Models\ProgramacionAcademica\CargaAcademica::class, \App\Policies\ProgramacionAcademica\CargaAcademicaPolicy::class);
        Gate::policy(\App\Models\ProgramacionAcademica\Maestros\Aula::class, \App\Policies\ProgramacionAcademica\Maestros\AulaPolicy::class);
        Gate::policy(\App\Models\ProgramacionAcademica\Maestros\TipoAula::class, \App\Policies\ProgramacionAcademica\Maestros\TipoAulaPolicy::class);
        Gate::policy(\App\Models\ProgramacionAcademica\Maestros\Seccion::class, \App\Policies\ProgramacionAcademica\Maestros\SeccionPolicy::class);
        Gate::policy(\App\Models\ProgramacionAcademica\Maestros\Horario::class, \App\Policies\ProgramacionAcademica\Maestros\HorarioPolicy::class);

        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
