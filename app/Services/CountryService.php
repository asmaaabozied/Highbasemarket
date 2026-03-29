<?php

namespace App\Services;

use App\Enum\CurrencyEnum;
use App\Enum\GccCountryEnum;
use App\Models\Branch;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CountryService
{
    public function isGccCountry(string $countryCode): bool
    {
        return in_array(strtoupper($countryCode), $this->gccCountryCodes());
    }

    private function gccCountryCodes(): array
    {
        return array_column(GccCountryEnum::cases(), 'value');
    }

    public function getGccCountries(): \Illuminate\Support\Collection
    {
        return $this->getCountries()->filter(fn (Country $country): bool => in_array($country->iso2,
            $this->gccCountryCodes()));
    }

    public function getCountries(): Collection
    {
        return Country::get(); // collect(json_decode(file_get_contents(resource_path('/js/Extends/countries.json')), true));

    }

    public function getCountryCurrency($country_id): CurrencyEnum
    {
        $country = $this->getCountryById($country_id);

        return isset($country['currency']) ? CurrencyEnum::from($country['currency']) : CurrencyEnum::USD;
    }

    public function getCountryById($country_id)
    {
        $countries = $this->getCountries();

        return $countries->where('id', $country_id)->first();
    }

    public function getStateByCountryId(?int $country_id = null): \Illuminate\Support\Collection
    {
        if (! $country_id) {
            return collect();
        }

        return State::query()->where('country_id', $country_id)->get();

    }

    public function getStateById($stateId): ?State
    {
        return State::query()->find($stateId);

    }

    public function getCityById($city_id): ?City
    {
        return City::where('id', $city_id)->first();
    }

    public function getCountryByName(string $name): ?City
    {
        return Country::query()->where('name', $name)->first();
    }

    public function getCountryByIso2(string $iso): ?Country
    {
        return Country::query()->where('iso2', $iso)->first();
    }

    public function countriesLookup(): Collection
    {
        return Country::query()->select('id', 'name')->get();
    }

    public function formattedBranchAddress(int $branchId): string
    {
        $branch  = Branch::query()->findOrFail($branchId);
        $address = $branch->address;

        if (empty($address) || ! array_key_exists('country', $address)) {
            return '';
        }
        $country  = $this->getCountryById($address['country']);
        $street   = $address['street'];
        $building = $address['building'];

        return Str::of('Building '.$building.', ')
            ->append('Street '.$street.', ')
            ->append($country->name);
    }

    public function getCitiesByCountry(int $countryId): Collection
    {
        return City::query()
            ->where('country_id', $countryId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    public function getCitiesByState(int $stateId): Collection
    {
        return City::query()
            ->where('state_id', $stateId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }
}
