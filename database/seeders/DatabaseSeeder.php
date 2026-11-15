<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Seeders\EstructuraGeografica\GeografiaSeeder;
use Database\Seeders\Personas\Maestros\PerfilSeeder;
use Database\Seeders\SeguridadYAccesos\Usuario\UserSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
          $this->call([
            GeografiaSeeder::class,
            PerfilSeeder::class,
            UserSeeder::class
        ]);
    }
}
