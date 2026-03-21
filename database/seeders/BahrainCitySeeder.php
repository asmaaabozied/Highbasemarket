<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class BahrainCitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = collect(json_decode(file_get_contents(resource_path('/js/Extends/bahrain_cities.json')), true));

        foreach ($cities as $city) {
            City::query()->updateOrCreate(
                [
                    'name'     => $city['name'],
                    'state_id' => $city['state_id'],
                ],
                $city
            );
        }
    }
}
