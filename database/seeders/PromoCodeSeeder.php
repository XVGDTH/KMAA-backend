<?php

namespace Database\Seeders;

use App\Models\PromoCode;
use Illuminate\Database\Seeder;

class PromoCodeSeeder extends Seeder
{
    public function run(): void
    {
        $promos = [
            [
                'code'          => 'ENTREPRISE10',
                'type'          => 'percent',
                'value'         => 10,
                'label'         => '-10% entreprises',
                'min_subtotal'  => 0,
                'min_team_size' => 1,
                'max_uses'      => null,
                'is_active'     => true,
            ],
            [
                'code'          => 'BULK15',
                'type'          => 'percent',
                'value'         => 15,
                'label'         => '-15% à partir de 5 apprenants',
                'min_subtotal'  => 0,
                'min_team_size' => 5,
                'max_uses'      => null,
                'is_active'     => true,
            ],
            [
                'code'          => 'PREMIUM20',
                'type'          => 'percent',
                'value'         => 20,
                'label'         => '-20% offre Premium',
                'min_subtotal'  => 0,
                'min_team_size' => 1,
                'max_uses'      => null,
                'is_active'     => true,
            ],
            [
                'code'          => 'FIDELITE',
                'type'          => 'fixed',
                'value'         => 50000,
                'label'         => '-50 000 FCFA',
                'min_subtotal'  => 200000,   // évite les abus sur petits paniers
                'min_team_size' => 1,
                'max_uses'      => null,
                'is_active'     => true,
            ],
            [
                'code'          => 'BIENVENUE',
                'type'          => 'percent',
                'value'         => 12,
                'label'         => '-12% nouvelle entreprise',
                'min_subtotal'  => 0,
                'min_team_size' => 1,
                'max_uses'      => null,
                'is_active'     => true,
            ],
        ];

        foreach ($promos as $p) {
            PromoCode::updateOrCreate(
                ['code' => $p['code']],
                $p
            );
        }
    }
}