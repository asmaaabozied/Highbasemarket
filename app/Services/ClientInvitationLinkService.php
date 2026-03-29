<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Support\Facades\URL;

class ClientInvitationLinkService
{
    public function create(
        string $route,
        Branch $branch,
        ?Employee $employee = null,
        string $key = '',
        string $type = 'customer'
    ) {
        return ShortUrlService::get(
            URL::signedRoute($route, [
                'branch'   => $branch->slug,
                'employee' => $employee?->id,
                'type'     => $type,
            ]),
            "$key-$branch->slug-$employee?->id"
        );
    }

    public function globalProfileLink(Branch $branch, ?Employee $employee = null)
    {
        return $this->create('storefront.profile', $branch, $employee, 'customer');
    }

    public function localStoreLink(Branch $branch, ?Employee $employee)
    {
        return $this->create('storefront.stores', $branch, $employee, 'store');
    }

    public function vendorLink(Branch $branch, ?Employee $employee)
    {
        return $this->create('storefront.profile', $branch, $employee, 'vendor', 'vendor');
    }

    public static function make(): self
    {
        return new self;
    }
}
