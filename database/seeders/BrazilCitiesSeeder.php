<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Fluent;
use Illuminate\Support\Facades\Cache;

class BrazilCitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estadosDoBrasil = collect(
            json_decode(
                file_get_contents(
                    database_path('seeders/data/estados-do-brasil.json')
                ),
                true
            )['data'] ?? null
        );

        $estadoPorUf = function (string $uf) use (
            $estadosDoBrasil,
        ): Fluent {
            if (!$uf) {
                return new Fluent([]);
            }

            return Cache::remember(
                'estadoPorUf-' . $uf,
                (60 * 60 * 24) /*secs*/,
                fn () => new Fluent($estadosDoBrasil->where('uf', $uf)->first())
            );
        };

        $cities = collect(
            json_decode(
                file_get_contents(
                    database_path('seeders/data/cidades-do-brasil.json')
                ),
                true
            )['data'] ?? null
        )
            ->map(function (array $item) use (
                $estadoPorUf,
            ) {
                return [
                    'name' => $item['name'] ?? null,
                    'city_code' => $item['codigo_ibge'] ?? null,
                    'state_code' => $item['state'] ?? null,
                    'state_name' => $item['state_name'] ?? $estadoPorUf('RO')->name,
                    'state_local_name' => $item['state_local_name'] ?? $estadoPorUf('RO')->name,
                    'country_iso_code' => $item['country_iso_code'] ?? 'BR',
                ];
            })
            ->filter();

        City::upsert(
            $cities->toArray(),
            [
                'city_code',
                'state_code',
                'country_iso_code',
            ]
        );
    }
}
