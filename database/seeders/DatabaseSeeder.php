<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndAdminsSeeder::class,
            CourseSeeder::class,                  // ✅ 12 sujets individuels
            EnterpriseCourseSeeder::class,        // 🆕 12 cours entreprises
            PricingPlanSeeder::class,             // ✅ 4 formules individuelles
            EnterprisePricingPlanSeeder::class,   // 🆕 4 formules entreprise
            PromoCodeSeeder::class
        ]);
    }
}