<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = collect(json_decode(file_get_contents(resource_path('/js/Extends/countries.json')), true));
        foreach ($countries as $country) {
            Country::query()
                ->updateOrCreate([
                    ...$country,
                    'timezones'    => $country['timezones'],
                    'translations' => $country['translations'],
                ]);
        }
    }
}
