<?php

namespace App\Services;

use App\Actions\AttachImage;
use App\Http\Filters\BranchesFilter;
use App\Http\Filters\BrandsFilter;
use App\Http\Filters\EmployeesFilter;
use App\Http\Filters\OrdersFilter;
use App\Http\Filters\ProductsFilter;
use App\Http\Filters\QuotesFilter;
use App\Http\Filters\RolesFilter;
use App\Http\Filters\StocksFilter;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\OrderLine;
use App\Models\Product;
use App\Models\Quote;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class BranchService
{
    public function __construct(private readonly ?Branch $branch = null, private readonly bool $isAccount = true) {}

    public function Brands()
    {
        $filter = new BrandsFilter;

        $data = $filter->execute(function ($query): void {
            $query->withCount('products');
        })
            ->forBranch($this->branch)->paginate();

        $data->getCollection()->transform(function ($brand) {
            $brand->ownership = $brand->owner_id === $this->branch->id ? 'owner' : 'distributor';

            return $brand;
        });

        return $data;
    }

    public function stocksBrands(): Collection
    {
        return Brand::whereHas('products', function ($query): void {
            $query->whereHas('stocks', function ($query): void {
                $query->where('branch_id', $this->branch->id);
            });
        })->get();
    }

    public function totalPurchases(): float
    {
        return OrderLine::whereHas('order', function ($query): void {
            $query->where('branch_id', $this->branch->id);
        })
            ->whereNotIn('status', ['rejected', 'cancelled', 'pending'])
            ->sum('total');

    }

    public function totalSales(): float
    {
        return OrderLine::whereHas('product', function ($query): void {
            $query->where('branch_id', $this->branch->id);
        })
            ->whereNotIn('status', ['rejected', 'cancelled', 'pending'])
            ->sum('total');
    }

    public function ordersCount(): int
    {
        return OrderLine::whereHas('product', function ($query): void {
            $query->where('branch_id', $this->branch->id);
        })
            ->whereNotIn('status', ['rejected', 'cancelled', 'pending'])
            ->count();
    }

    public function purchasesCount(): int
    {
        return OrderLine::whereHas('order', function ($query): void {
            $query->where('branch_id', $this->branch->id);
        })
            ->whereNotIn('status', ['rejected', 'cancelled', 'pending'])
            ->count();
    }

    public function receivedQuotesCount(): int
    {
        return Quote::where('vendor', $this->branch->id)->count();
    }

    public function sentQuotesCount(): int
    {
        return Quote::where('creator', $this->branch->id)->count();
    }

    public function Products()
    {
        $filter = new ProductsFilter;

        return $filter->
        execute(function ($query): void {
            $query->withCount('variants');
        })
            ->forBranch($this->branch)
            ->withCategory()
            ->withBrand()
            ->withGroup()
            ->paginate();
    }

    public function ProductsCount(): int
    {
        return Product::whereHas('brand', function ($query): void {
            $query->where('owner_id', $this->branch->id);
        })->count();
    }

    public function orders()
    {
        $filter = new OrdersFilter;

        $data = $filter->forSeller($this->branch)
            ->withAssignees($this->branch)
            ->withBuyer($this->isAccount, $this->branch)
            ->withItemsCount($this->branch)
            ->paginate();

        $data->getCollection()->transform(function (\App\Models\Order $order): \App\Models\Order {
            $order->status     = OrdersService::OrderStatus($order);
            $order->commission = OrdersService::OrderCommission($order);
            $order->assignees  = $order->assigns->pluck('assignee.user');

            unset($order->lines);

            return $order;
        });

        return $data;
    }

    public function purchases()
    {
        $filter = new OrdersFilter;

        $data = $filter->forBranch($this->branch)->withCreator()->withItemsCount()->paginate();

        $data->getCollection()->transform(function (\App\Models\Order $purchase): \App\Models\Order {
            $purchase->status = OrdersService::OrderStatus($purchase);

            return $purchase;
        });

        return $data;
    }

    public function stocks()
    {
        $filter = new StocksFilter;

        $data = $filter->execute(function ($query): void {
            $query
                ->with([
                    'product.category:id,slug,name',
                    'product.brand:id,slug,name',
                    'variant',
                ]);
        })->forBranch($this->branch)->paginate();

        $data->setCollection(StockService::format($data->getCollection()));

        return $data;
    }

    public function sentQuotes()
    {
        return (new QuotesFilter)
            ->whereCreator($this->branch)
            ->execute(function ($query): void {
                $query->with('vendor_branch:id,name,slug');
            })->paginate();

    }

    public function receivedQuotes()
    {
        return (new QuotesFilter)
            ->whereVendor($this->branch)
            ->execute(function ($query): void {
                $query->with('creator_branch:id,name,slug');
            })->paginate();
    }

    public function roles(): \Illuminate\Database\Eloquent\Collection|array|null
    {
        return (new RolesFilter)
            ->forAccount($this->branch->account)
            ->get();
    }

    public function vendors()
    {
        return (new BranchesFilter)
            ->execute(function ($query): void {
                $query->select('id', 'name', 'slug', 'address')
                    ->whereHas('myCustomers', function ($query): void {
                        $query->where('customer_id', $this->branch->id);
                    });
            })->paginate();
    }

    public function subscription() {}

    public function wallet() {}

    public function getEmployeeBranches(): Collection|array
    {
        return Account::query()
            ->select('branches.id', 'branches.name', 'branches.address->state as state')
            ->where('accounts.id', auth()->user()?->userable->account_id)
            ->whereNull('branches.deleted_at')
            ->join('branches', 'branches.account_id', 'accounts.id')
            ->get();
    }

    public function getBranchByAccountName()
    {
        return Account::query()
            ->select('branches.*')
            ->where('accounts.id', auth()->user()?->userable->account_id)
            ->whereNull('branches.deleted_at')
            ->join('branches', function ($join): void {
                $join->on(DB::raw('REPLACE(branches.name, \'"\', \'\')'), 'accounts.name');
            })
            ->first();
    }

    public function updateBranch(array $data, $branch): void
    {
        $branch->update([
            ...$data,
            'config' => isset($data['config']) ? Arr::except($data['config'], 'delivery_locations') : $branch->config,
        ]);

        if (isset($data['image']) && ! URL::isValidUrl($data['image'])) {
            AttachImage::attach($branch, collection: 'branches', field: 'image');
        }

        if (isset($data['cr_image']) && ! URL::isValidUrl($data['cr_image'])) {
            AttachImage::attach($branch, collection: 'crs', field: 'cr_image');
        }

        if (isset($data['vat_certificate']) && ! URL::isValidUrl($data['vat_certificate'])) {
            AttachImage::attach($branch, collection: 'vat_certificates', field: 'vat_certificate');
        }

        if (isset($data['cover_image']) && ! URL::isValidUrl($data['cover_image'])) {
            AttachImage::attach($branch, collection: 'covers', field: 'cover_image');
        }
    }

    public function createBranch(array $data)
    {
        $branch = Branch::query()->create($data);

        if (isset($data['image']) && ! URL::isValidUrl($data['image'])) {
            AttachImage::attach($branch, 'branches');
        }

        if (isset($data['cr_image']) && ! URL::isValidUrl($data['cr_image'])) {
            AttachImage::attach($branch, collection: 'crs', field: 'cr_image');
        }

        if (isset($data['vat_certificate']) && ! URL::isValidUrl($data['vat_certificate'])) {
            AttachImage::attach($branch, collection: 'vat_certificates', field: 'vat_certificate');
        }

        if (isset($data['cover_image']) && ! URL::isValidUrl($data['cover_image'])) {
            AttachImage::attach($branch, collection: 'covers', field: 'cover_image');
        }

        return $branch;
    }

    public function getBranchLookup(): \Illuminate\Support\Collection
    {
        return Branch::query()
            ->select('id', 'name')
            ->where('status', 'active')
            ->orderByDesc('id')
            ->get();
    }

    public function getBranchEmployees()
    {
        $select_fields = [
            'employees.id', DB::raw("CONCAT(users.first_name, ' ', users.last_name) as full_name"), 'users.phone',
        ];

        return currentBranch()->employees()
            ->select($select_fields)
            ->join('users', 'users.userable_id', 'employees.id')
            ->get()
            ->merge(
                currentBranch()->account->employees()
                    ->select($select_fields)
                    ->join('users', 'users.userable_id', 'employees.id')
                    ->get()
            );
    }

    public function employees()
    {
        $filter = new EmployeesFilter;

        return $filter
            ->execute(function ($query): void {
                $query
                    ->where('account_id', $this->branch->account_id)
                    ->with([
                        'user' => function ($query): void {
                            $query
                                ->select('id', 'first_name', 'last_name', 'userable_id', 'userable_type',
                                    'profile_photo_path')
                                ->with('roles:id,name,slug');
                        },
                    ]);

                $query->where(function ($q): void {
                    $q->whereHas('branches', function ($q): void {
                        $q->where('branches.id', $this->branch->id);
                    })
                        ->orWhereDoesntHave('branches');
                });
            })
            ->paginate();
    }

    public function saveOrUpdateAddress($branch, $data): void
    {
        $hasValue = fn ($arr) => collect($arr)->flatten()->filter()->isNotEmpty();

        $addresses = collect($data)->map(fn ($item) => collect($item)->except('employee')->all())->filter($hasValue);

        $employees = collect($data)->pluck('employee');

        DB::transaction(function () use ($addresses, $employees, $branch): void {
            foreach ($addresses as $key => $address) {
                $createAddress = $branch->addresses()->updateOrCreate(
                    ['id' => $address['id'] ?? null],
                    $address
                );

                if ($employees->isNotEmpty()) {
                    $employee = $employees[$key];

                    if (empty($employee['employee_id'])) {

                        if (! empty($employee['id'])) {
                            $createAddress->employee()
                                ->where('id', $employee['id'])
                                ->delete();
                        }

                        continue;
                    }

                    unset($employee['id']);

                    $createAddress->employee()->updateOrCreate(
                        ['branch_address_id' => $createAddress->id],
                        Arr::except($employee, 'contact_number')
                    );
                }
            }
        });
    }

    public function saveOrUpdateDeliveryLocation($branch, $states)
    {
        foreach ($states as $state) {
            $branch->deliveryLocations()->updateOrCreate(
                ['id' => $state['id'] ?? null],
                $state
            );
        }

        return $branch;
    }

    public function getBranchDeliveryLocations($branch)
    {
        return $branch->deliveryLocations()->select('id', 'name', 'state_id', 'branch_id', 'cities', 'selected_city')
            ->get();
    }
}
